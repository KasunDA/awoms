<?php
/****
// DO NOT MODIFY THIS FILE!
// Instead you need to edit 'dbConfig' and 'customConfig'
****/
$dbFile = ROOT . DS . 'config' . DS . 'dbConfig.php';
$customConfigFile = ROOT . DS . 'config' . DS . 'customConfig.php';
if (!is_file($dbFile) || !is_file($customConfigFile))
{
    require(ROOT . DS . 'config' . DS . 'help.php');
    die();
}

// Load DB Config
require_once($dbFile);

// Logs
ini_set('log_errors', 'On');
ini_set('error_log', ROOT . DS . 'tmp' . DS . 'logs' . DS . 'error.log');

// Load Error Config
require_once($customConfigFile);

// Error handling
require_once(ROOT . DS . 'library' . DS . 'errors.class.php');
error_reporting(-1);
set_error_handler(array('Errors', 'captureNormal'));
set_exception_handler(array('Errors', 'captureException'));
register_shutdown_function(array('Errors', 'captureShutdown'));

// Autoloader
require_once(ROOT . DS . 'library' . DS . 'autoloader.class.php');
spl_autoload_register(array('Autoloader', 'loadClass'));

// Encoding
mb_internal_encoding('UTF-8');

// Load Version
$versionFile = ROOT.DS.'config'.DS.'version.txt';
if (file_exists($versionFile)){$version = file_get_contents($versionFile);}else{$version = "v00.00.00";}
define('ProductName', 'GPFC PHP MVC CMS');
define('Version', $version);
