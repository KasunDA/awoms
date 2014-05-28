<?php
/****
// No need to edit this file
// Instead you need to edit 'dbConfig' and 'extraConfig'
****/

// Load DB Config
require_once(ROOT . DS . 'config' . DS . 'dbConfig.php');

// Logs
ini_set('log_errors', 'On');
ini_set('error_log', ROOT . DS . 'tmp' . DS . 'logs' . DS . 'error.log');

// Load Error Config
require_once(ROOT . DS . 'config' . DS . 'extraConfig.php');

// Error handling
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

// Load Version
define('ProductName', 'AWOMS PHP MVC');
define('Version', file_get_contents(ROOT.DS.'config'.DS.'version.txt'));