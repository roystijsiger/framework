<?php

abstract class Module {
    static public $Data = array();

    public function GetControllerName(){
        $className = get_called_class();
        
        $pos = strpos($className, 'Controller');
        if(!$pos){
            exit("Controllers must end with 'Controller'");
        }
       
        return substr($className, 0, $pos);
    }
    
    protected function OkResult($view, $template = null) {
        return $this->HttpResult(200, $view, $template);
    }

    protected function RedirectResult($location) {
        return new HttpResult(301, array('Location' => $location));
    }

    protected function BadRequestResult($view, $template = null) {
        return $this->HttpResult(200, $view, $template);
    }

    protected function NotFoundResult($view, $template = null) {
        return $this->HttpResult(404, $view, $template);
    }

    protected function ServerErrorResult($view, $template = null) {
        return $this->HttpResult(500, $view, $template);
    }

    protected function JsonOkResult($object, $statuscode = 200, $additionalHeaders = array()){
        $additionalHeaders['Content-Type'] = 'application/json';

        return new HttpResult($statuscode, $additionalHeaders, json_encode($object));
    }
    
    protected function HttpResult($statuscode, $view, $template = null, $additionalHeaders = array(), $viewPath = null) {
        if($viewPath === null){
            $viewPath = $this->GetControllerName();
        }

        $result = Views::Create($viewPath, $view, self::$Data, $template);

        return new HttpResult($statuscode, $additionalHeaders, $result);
    }
    
}
