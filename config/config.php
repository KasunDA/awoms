<?php
// Configuration Variables
if ($_SERVER['SERVER_ADDR'] == '127.0.0.1') {
  define ('DEVELOPMENT_ENVIRONMENT',TRUE); // SET TO TRUE
  define('ERROR_LEVEL', 9);
  define('APIURL', 'http://dev.awoms.com/');
} else {
  define ('DEVELOPMENT_ENVIRONMENT',FALSE);
  define('ERROR_LEVEL', 5);
  define('APIURL', 'http://api.awoms.com/');
}

// Load DB Config
include(ROOT.DS.'config/dbconfig.php');

// Logs
ini_set('log_errors', 'On');
ini_set('error_log', ROOT . DS . 'tmp' . DS . 'logs' . DS . 'error.log');

// Error handling
define('ERROR_EMAIL', 'errors@awoms.com');
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