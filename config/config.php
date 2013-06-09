<?php
// Configuration Variables
define ('DEVELOPMENT_ENVIRONMENT',true);
define('DB_NAME', 'awoms');
define('DB_USER', 'awoms');
define('DB_PASSWORD', 'dra?retr?pep-s+uvu92-g!p-2aswe3refretE**fr$-hus8anase9r+swate');
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');

// Logs
ini_set('log_errors', 'On');
ini_set('error_log', ROOT . DS . 'tmp' . DS . 'logs' . DS . 'error.log');

// Error handling
define('ERROR_LEVEL', 9);
define('ERROR_EMAIL', 'brock@awoms.com');
require_once(ROOT . DS . 'library' . DS . 'errors.class.php');
error_reporting(-1);
set_error_handler(array('Errors', 'captureNormal'));
set_exception_handler(array('Errors', 'captureException'));
register_shutdown_function(array('Errors', 'captureShutdown'));    
ini_set('display_errors', 'On');

// Autoloader
require_once(ROOT . DS . 'library' . DS . 'autoloader.class.php');
spl_autoload_register(array('Autoloader', 'loadClass'));

// Encoding
mb_internal_encoding('UTF-8');