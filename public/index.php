<?php
// Global definitions
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(dirname(__FILE__)));

// Load Config
require_once (ROOT . DS . 'config' . DS . 'config.php');

// URL from ../.htaccess
$global = array();
if (!empty($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == "POST")
{
    $global['input'] = $_POST;    
}
else
{
    $global['input'] = $_GET;
}

// Default Page or Requested URL (sanitized)
$defaultPage = "home";
$global['input']['url'] = strtolower(filter_input(INPUT_GET, "url", FILTER_SANITIZE_STRING)) ?: $defaultPage;

// Handle page request via Bootstrap MVC
require_once (ROOT . DS . 'library' . DS . 'bootstrap.class.php');

Errors::debugLogger("*************** RAW URL: " . serialize($global['input']));

$m = NULL;
if (!empty($global['input']['m']))
{
    $m = $global['input']['m'];
}

// GO!
$BS = new Bootstrap($global['input']['url'], $m);

// Debug
if (DEVELOPMENT_ENVIRONMENT
        && ERROR_LEVEL >= 9
        && empty($global['input']['m'])) {
?>
<div style='clear:both; width:90%; margin-left: 50px; margin-top:200px; background-color:yellow; color:red; text-align:left; font-size:10px;'>
    <hr />
    <h3>Footer Debug:</h3>
<?php
  var_dump($global, BRAND, BRAND_URL, $BS);
  if (!empty($_SESSION)) {
      echo "<hr /><h3>Session:</h3>";
      var_dump($_SESSION);
  }
  echo "</div>";
}