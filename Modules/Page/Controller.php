<?php

class PageController extends Module {
    function __construct() {
        // Hook events to methods
        Modules::HookEvent('routes.pageload', $this, 'PageLoad');

        // Add routes
        Routes::AddNotFound($this, 'NotFound');
        Routes::AddServerError($this, 'ServerError');

        Routes::Get('', $this, 'RedirectToLogin');
        Routes::Get('nl', $this, 'RedirectToIndex');
        Routes::Get('menu', $this, 'ListMenu');
        Routes::Get('menu/edit', $this, 'EditMenuItem');
        Routes::Post('menu/edit', $this, 'MenuItemPost');
        Routes::Get('menu/additem', $this, 'EditMenuItem');
        Routes::Get('context/change', $this, 'ChangeContext');
        Routes::Get('forbidden', $this, 'Forbidden');
    }
    
    public function PageLoad($route) {
        $uid = isset($_SESSION['user']) && isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : null;

        if(isset($route['route']) && !Permissions::Check($uid, 'routing', $route['route'])){
            return $this->RedirectResult('/forbidden');
        }

        $menu = json_decode(json_encode(DB::table('pages_menu')->whereNull('parent')->where('hidden', false)->orderBy('order')->get()), true);
        $nestedMenu = array();
        
        foreach ($menu as $item) {
            if(Permissions::Check($uid, 'menu', $item['route']))
            {
                $children = json_decode(json_encode(DB::table('pages_menu')->where('hidden', false)->where('parent', $item['id'])->get()), true);
                foreach ($children as $child)
                {
                    if(Permissions::Check($uid, 'menu', $child['route'])){
                        $item['children'][] = $child;
                    }
                }

                $nestedMenu[$item['id']] = $item;
            }
        }

        self::$Data['Menu'] = $nestedMenu;
        self::$Data['user']['id'] = $uid;
        if(!isset($_SESSION['context'])){
            
            $_SESSION['context']['month'] = date('n');
            $_SESSION['context']['year'] = date('Y');
            $_SESSION['context']['client'] = 0;
        }
        self::$Data['context'] = $_SESSION['context'];        
    }

    public function ChangeContext($data) {
          
        foreach ($data as $key => $value) {
            switch ($key) {
                case "month":
                    $_SESSION['context']['month'] = $value;
                    break;
                case "year":
                    $_SESSION['context']['year'] = $value;
                    break;
                case "client":
                    $_SESSION['context']['client'] = $value;
                    break;
            }
        }
        return $this->RedirectResult($_SERVER['HTTP_REFERER']);
    }

    public function ListMenu() {
        return $this->OkResult('menuList');
    }

    public function EditMenuItem($data) {
        if (!isset($data['id'])) {
            // Assume we are creating a new one
            return $this->OkResult('editMenuItem');
        }

        $item = DB::table('pages_menu')->find($data['id']);

        if ($item === null) {
            return $this->NotFoundResult('notFound');
        }

        self::$Data['menuItem'] = $item;
        return $this->OkResult('editMenuItem');
    }

    public function MenuItemPost($data) {
        $newitem = !isset($data['id']) || empty($data['id']);

        if (!$newitem) {
            $item = DB::table('pages_menu')->find($data['id']);

            if ($item === null) {
                return $this->NotFoundResult('notFound');
            }
        }

        $itemData = array(
            'parent' => empty($data['parent']) ? null : $data['parent'],
            'order' => $data['order'],
            'name' => $data['name'],
            'description' => $data['description'],
            'route' => $data['route'],
            'icon' => $data['icon'],
            'hidden' => isset($data['hidden']) ? 1 : 0
        );

        if ($newitem)
            DB::table('pages_menu')->insert($itemData);
        else
            DB::table('pages_menu')
                    ->where('id', $data['id'])
                    ->update($itemData);

        return $this->RedirectResult('/menu');
    }

    public function Index() {
        self::$Data['title'] = "Welkom op het online zorgdossier!";

        return $this->OkResult('index', 'home');
    }

    public function RedirectToLogin() {
        return $this->RedirectResult('/account/login');
    }

    public function RedirectToIndex() {
        return $this->RedirectResult('/');
    }

    public function NotFound() {
        return $this->NotFoundResult('404');
    }

    public function ServerError($data) {
        self::$Data['error'] = $data['exception'];
        //self::$Data['error']['message'] = $data['exception']->getMessage();
        //self::$Data['error']['stacktrace'] = $data['exception']->getTrace();

        return $this->ServerErrorResult('500');
    }

    public function Forbidden(){
       return $this->HttpResult(403, '403');
    }
}
