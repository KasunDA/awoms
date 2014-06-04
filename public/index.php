<?php
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

Errors::debugLogger("*************** RAW URL: " . serialize($global['input']));

$m = NULL;
if (!empty($global['input']['m']))
{
    Errors::debugLogger("*************** RAW M: " . $global['input']['m']);
    $m = $global['input']['m'];
}
$BS = new Bootstrap($global['input']['url'], $m);

// Debug
if (ERROR_LEVEL >= 9
  && empty($global['input']['m'])) {
    echo "<hr /><h3>Footer Debug:</h3>";
  var_dump($global, BRAND, BRAND_URL);
  if (!empty($_SESSION)) {
      echo "<hr /><h3>Session:</h3>";
      var_dump($_SESSION);
  }
}
