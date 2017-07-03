<?php
// General settings
define('HOSTNAME', '');
define('ROOT_DIR', __DIR__ . '/');
define('UPLOAD_DIR', ROOT_DIR . 'UserUpload/');
define('VERSION', '0.0.3');
define('DEBUG', true);

define('ROOT_URL', 'http://dev.' . HOSTNAME . '/');
define('UPLOAD_URL', ROOT_URL . 'UserUpload/');

// Mail settings
define('MAIL_FROM', 'support@' . HOSTNAME);
define('MAIL_SUPPORT', '');

define('MAIL_SMTP_SERVER', 'mail.' . HOSTNAME);
define('MAIL_SMTP_USERNAME', MAIL_FROM);
define('MAIL_SMTP_PASSWORD', '');

// Database settings
define('DB_HOST', 'localhost');
define('DB_NAME', '');
define('DB_USER', '');
define('DB_PASS', '');

require_once 'Initialize.php';
