<?php
class FileManager
{
    private $Roots;

    public function __construct($route, $name = 'Hoofdmap'){
        include_once ROOT_DIR . 'Libraries/elfinder/php/elFinderConnector.class.php';
        include_once ROOT_DIR . 'Libraries/elfinder/php/elFinder.class.php';
        include_once ROOT_DIR . 'Libraries/elfinder/php/elFinderVolumeDriver.class.php';
        include_once ROOT_DIR . 'Libraries/elfinder/php/elFinderVolumeLocalFileSystem.class.php';

        $this->Roots = array(
            array(
                'driver' => 'LocalFileSystem', // driver for accessing file system (REQUIRED)
                'path' => UPLOAD_DIR . $route, // path to files (REQUIRED)
                'URL' => UPLOAD_URL . $route, // URL to files (REQUIRED)
                'alias' => $name,
                //'uploadDeny' => array('all'), // All Mimetypes not allowed to upload
                //'uploadAllow' => array('image', 'text/plain'), // Mimetype `image` and `text/plain` allowed to upload
                //'uploadOrder' => array('deny', 'allow'), // allowed Mimetype `image` and `text/plain` only
                //'accessControl' => 'access'                     // disable and hide dot starting files (OPTIONAL)
            )
        );
    }

    public function AddRoute($route, $name = 'Hoofdmap'){
        $Roots[] = array(
            'driver' => 'LocalFileSystem', // driver for accessing file system (REQUIRED)
            'path' => UPLOAD_DIR, // path to files (REQUIRED)
            'URL' => ROOT_URL . UPLOAD_DIR . $route, // URL to files (REQUIRED)
            'alias' => $name
            //'uploadDeny' => array('all'), // All Mimetypes not allowed to upload
            //'uploadAllow' => array('image', 'text/plain'), // Mimetype `image` and `text/plain` allowed to upload
            //'uploadOrder' => array('deny', 'allow'), // allowed Mimetype `image` and `text/plain` only
        );
    }

    public function Run(){
        $opts = array(
            'roots' => $this->Roots
        );

        //dump($opts);

        $connector = new elFinderConnector(new elFinder($opts));
        $connector->run();
    }
}