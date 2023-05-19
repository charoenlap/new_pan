<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
 error_reporting(0);

// HTTP
define('HTTP_SERVER_MAIN', 'https://www.sportstown-online.com/');
define('HTTP_SERVER', 'https://www.sportstown-online.com/arena_thailand/');

// HTTPS
define('HTTPS_SERVER_MAIN', 'https://www.sportstown-online.com/');
define('HTTPS_SERVER', 'https://www.sportstown-online.com/arena_thailand/');

// DIR
define('DIR_APPLICATION', '/var/www/vhosts/sportstown-online.com/httpdocs/arena_thailand/catalog/');
define('DIR_SYSTEM', '/var/www/vhosts/sportstown-online.com/httpdocs/system/');
define('DIR_IMAGE', '/var/www/vhosts/sportstown-online.com/httpdocs/image/');
define('DIR_IMAGE2', HTTP_SERVER_MAIN.'/image/');
define('DIR_STORAGE', DIR_SYSTEM . 'storage/');
define('DIR_LANGUAGE', DIR_APPLICATION . 'language/');
define('DIR_TEMPLATE', DIR_APPLICATION . 'view/theme/');
define('DIR_CONFIG', DIR_SYSTEM . 'config/');
define('DIR_CACHE', DIR_STORAGE . 'cache/');
define('DIR_DOWNLOAD', DIR_STORAGE . 'download/');
define('DIR_DOWNLOAD2', '/var/www/vhosts/sportstown-online.com/storage/download/');
define('DIR_LOGS', DIR_STORAGE . 'logs/');
define('DIR_MODIFICATION', DIR_STORAGE . 'modification/arena_thailand/');
define('DIR_SESSION', DIR_STORAGE . 'session/');
define('DIR_UPLOAD', DIR_STORAGE . 'upload/');

// DB
define('DB_DRIVER', 'mysqli');
define('DB_HOSTNAME', 'localhost');
define('DB_USERNAME', 'admin_town');
define('DB_PASSWORD', 'Online1234');
define('DB_DATABASE', 'admin_town');
define('DB_PORT', '3306');
define('DB_PREFIX', 'oc_');