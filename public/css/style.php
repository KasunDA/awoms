<?php
// @TODO....
die("To do...");

// Global definitions
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(dirname(__FILE__).DS.'..'.DS.'..'.DS.'..'.DS));

// Load Config
require_once (ROOT . DS . 'config' . DS . 'config.php');

$cssFile = ROOT . DS . 'application' . DS . 'views' . DS . 'templates' . DS . BRAND_ID . DS . BRAND_THEME . DS . 'css' . DS . BRAND_ID . '.css';

if (is_file($cssFile)) {
  header("Content-type: text/css; charset: UTF-8");
  header("Content-length: " . filesize($cssFile));
  readfile($cssFile);
} else {
    echo "no ".$cssFile;
}