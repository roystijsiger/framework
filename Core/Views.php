<?php

class Views {

    private static $Twig;

    public static function Init() {
        $loader = new Twig_Loader_Filesystem('Views');
        self::$Twig = new Twig_Environment($loader, array(
            'cache' => 'Cache/twig',
            'debug' => DEBUG
        ));
    }

    public static function Create($moduleName, $view, $data, $template) {
        $template = $template != null ? $template : 'default';
 
        try {
            //self::$Twig->addGlobal('base', $twig->loadTemplate('base.macro'));

            return self::$Twig->render($moduleName . '/' . $view . '.html', array(
                'template' => 'Templates/' . $template . '.html',
                'data' => $data,
                'version' => VERSION,
                'debug' => DEBUG                
            ));
        } catch (Exception $e) {
            dump($e->getMessage());
        }
    }

}
