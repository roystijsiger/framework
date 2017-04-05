<?php
/**
 * OZ Admin 
 * (c) 2016 Zycon
 * 
 * @author Varbit
 * 
 */

// General settings
define('HOSTNAME', 'ozadmin.nl');
define('ROOT_DIR', __DIR__ . '/');
define('UPLOAD_DIR', ROOT_DIR . 'UserUpload/');
define('VERSION', '0.0.3');
define('DEBUG', true);

define('ROOT_URL', 'http://dev.' . HOSTNAME . '/');
define('UPLOAD_URL', ROOT_URL . 'UserUpload/');

// Mail settings
define('MAIL_FROM', 'support@' . HOSTNAME);
define('MAIL_SUPPORT', 'support@ozadmin.nl');

define('MAIL_SMTP_SERVER', 'mail.' . HOSTNAME);
define('MAIL_SMTP_USERNAME', MAIL_FROM);
define('MAIL_SMTP_PASSWORD', 'iP9MFq1P3a');

// Database settings
define('DB_HOST', 'localhost');
define('DB_NAME', 'zorgadmin_dev');
define('DB_USER', 'zorgadmin_oza');
define('DB_PASS', 'igNaveRCedIf');

require_once 'Initialize.php';