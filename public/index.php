<?php

// Global definitions
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(dirname(__FILE__)));

// Config
require_once (ROOT . DS . 'config' . DS . 'config.php');

// Read $_GET request
if (!empty($_GET['brand'])) {
  // Get Brand from htaccess rewrite
  $brand = $_GET['brand'];
} else {
  // Invalid? htaccess rewrite not working?
  $brand = str_replace('.com', '', $_SERVER['HTTP_HOST']);
}
if (!empty($_GET['url'])) {
  // Get Url from htaccess rewrite
  $url = $_GET['url'];
} else {
  // Invalid? htaccess rewrite not working?
  $url = 'home';
}

// For local development we send brand in url otherwise brand is taken from htaccess
if ($brand == 'localhost') {
  $matches = explode('/', $url);
  $brand = $matches[0];
  $url = str_replace($brand.'/', '', $url);
  define('BRANDURL', 'http://localhost/brockhensley-mvc/'.$brand.'/');
} else {
  // Remove '.com' from brand received from HTTP_HOST/htaccess
  $brand = str_replace('.com', '', $brand);
  define('BRANDURL', 'http://'.$_SERVER['HTTP_HOST'].'/');
}
$brand = str_replace('dev.', '', $brand);
define('BRAND', $brand);

// Handle page request via Bootstrap
require_once (ROOT . DS . 'library' . DS . 'bootstrap.class.php');
new Bootstrap($url);