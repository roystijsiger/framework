<?php
class Routes {
    private static $Routes = array();
    private static $NotFoundRoute;
    private static $ServerErrorRoute;

    public static function Init($method, $url, $data) {
        Module::$Data['routeurl'] = trim($url, '/');
        
        $routes = array_filter(self::$Routes, function($route) use($method, $url) {
            if ($route['route'] != trim($url, '/'))
                return false;
            
            if ($route['method'] != strtoupper($method))
                return false;
            
            return true;
        });

        switch (count($routes)) {
            case 0:
                $route = self::$NotFoundRoute;
                break;
            case 1:
                $route = reset($routes);
                break;
            default:
                $route = self::$ServerErrorRoute;
                break;
        }
        
        self::ExecuteRoute($route, $data);
    }

    public static function Get($route, $controller, $action = null)
    {
        self::add($route, $controller, $action, 'get');
    }

    public static function Post($route, $controller, $action = null)
    {
        self::add($route, $controller, $action, 'post');
    }

    public static function AddNotFound($controller, $action) {
        self::$NotFoundRoute = array(
            'controller' => $controller,
            'action' => $action
        );
    }

    public static function AddServerError($controller, $action) {
        self::$ServerErrorRoute = array(
            'controller' => $controller,
            'action' => $action
        );
    }

    public static function ExecuteHttpResult($httpResult) {
        http_response_code($httpResult->Statuscode);

        foreach ($httpResult->AdditionalHeaders as $headerName => $headerValue) {
            header($headerName . ': ' . $headerValue);
        }

        exit($httpResult->Response);
    }

    private static function add($route, $controller, $action = null, $method){
        self::$Routes[] = array(
            'route' => trim($route, '/'),
            'controller' => $controller,
            'action' => $action,
            'method' => strtoupper($method)
        );
    }

    private static function ExecuteRoute($route, $data){
        if ($route['action'] !== null) {
            try{
                Modules::PushEvent('routes.pageload', $route);
                $actionResult = $route['controller']->{$route['action']}($data);
            }
            catch (Exception $e){  
                $data['exception'] = $e;
                self::ExecuteServerError(self::$ServerErrorRoute, $data);
            }
            
            if ($actionResult !== null) {
                self::ExecuteHttpResult($actionResult);
            }
        }

        exit('Routing error: "No result at end of request"');
    }
    
    private static function ExecuteServerError($route, $data){
        if ($route['action'] !== null) {
            try{
                Modules::PushEvent('routes.servererror', $data);
                $actionResult = $route['controller']->{$route['action']}($data);
            }
            catch (Exception $e){  
                $data['exception'] = $e;
                http_response_code(500);
                dump($e->getMessage());
                dump($e->getTraceAsString());
                exit();
            }

            if ($actionResult !== null) {
                self::ExecuteHttpResult($actionResult);
            }
        }        
    }
}
