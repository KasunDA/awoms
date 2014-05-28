<?php
/**
 * Load: Config
 * Read: Brand, Url
 * Set: DS, ROOT, BRAND, BRAND_URL
 * Call: Bootstrap
 */

// Global definitions
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(dirname(__FILE__)));

// Load Config
require_once (ROOT . DS . 'config' . DS . 'config.php');

// URL from ../.htaccess
$global = array();
if ($_SERVER['REQUEST_METHOD'] == "GET")
{
    $global['input'] = $_GET;
}
elseif ($_SERVER['REQUEST_METHOD'] == "POST")
{
    $global['input'] = $_POST;
}
else
{
    trigger_error("Unknown Request Method '".$_SERVER['REQUEST_METHOD']."'");
    die();exit();
}

// Default Page or Requested URL (sanitized)
$defaultPage = "home";
$global['input']['url'] = filter_input(INPUT_GET, "url", FILTER_SANITIZE_STRING) ?: $defaultPage;

// Handle page request via Bootstrap MVC
require_once (ROOT . DS . 'library' . DS . 'bootstrap.class.php');
new Bootstrap($global['input']['url']);

// Debug
if (ERROR_LEVEL >= 9
  && empty($global['input']['m'])) {
    echo "<hr /><h5>Debug:</h5>";
  var_dump($global, BRAND, BRAND_URL);
}