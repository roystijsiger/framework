<?php

class Database {

    public static $Connection;

    static function Init() {
        $config = array(
            'driver' => 'mysql',
            'host' => DB_HOST,
            'database' => DB_NAME,
            'username' => DB_USER,
            'password' => DB_PASS,
            'charset' => 'utf8'
        );
        
        $connection = new \Pixie\Connection('mysql', $config, 'DB');
    }

}
