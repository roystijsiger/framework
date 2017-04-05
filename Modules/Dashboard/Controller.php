<?php

class DashboardController extends Module {
    function __construct() {

        Routes::Get('dashboard', $this, 'Dashboard');
    }

    public function Dashboard($data) {
        self::$Data['show_welcome'] = isset($data['loginsuccess']) && $data['loginsuccess'] == 1;
        //PermissionsController::Check(7,"dashboard" ,"view"); 
        return $this->OkResult('Dashboard');
    }
}
