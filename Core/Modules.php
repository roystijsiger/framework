<?php

class Modules {

    public static $Modules = array();
    public static $Hooks = array();

    public static function Init() {
        $modulesArray = array(
            'Page',
            'Account',
            'Person',
            'Report',
            'Target',
            'WorkingHours',
            'Dashboard',
            'Feedback',
            'Files',
            'Relation'
        );

        require_once 'Models/Module.php';
        require_once 'Models/HttpResult.php';

        foreach ($modulesArray as $module) {
            $files = glob('Modules/' . $module . '/*.php');

            foreach ($files as $filename) {
                require_once $filename;
            }

            $className = $module . 'Controller';

            if (class_exists($className)) {
                self::$Modules[$module] = new $className();
            }
        }
    }

    public static function HookEvent($event, $controller, $method) {
        self::$Hooks[$event][] = array(
            'module' => $controller->GetControllerName(),
            'method' => $method
        );
    }

    public static function PushEvent($event, $data) {
        if (!isset(self::$Hooks[$event]))
            return;

        foreach (self::$Hooks[$event] as $hook) {
            $module = self::$Modules[$hook['module']];
            if ($module != null) {
                $hookResult = $module->{$hook['method']}($data);

                if (get_class($hookResult) === 'HttpResult') {
                    Routes::ExecuteHttpResult($hookResult);
                }
            }
        }
    }

}
