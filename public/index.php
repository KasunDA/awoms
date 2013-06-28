<?php
/**
 * Load: Config
 * Read: Brand, Url
 * Set: DS, ROOT, BRAND, DOMAINURL
 * Call: Bootstrap
 */

// Global definitions
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(dirname(__FILE__)));

// Load Config
require_once (ROOT . DS . 'config' . DS . 'config.php');

// Read $_GET request from htaccess rewrite
if (!filter_has_var(INPUT_GET, 'domain')
  && !filter_has_var(INPUT_GET, 'url')) {
  Errors::debugLogger(1, 'Invalid request @ '.__FILE__.':'.__LINE__);
  trigger_error('Invalid request', E_USER_ERROR);
}
$domain = str_replace('dev.', '', $_GET['domain']);
$url = $_GET['url'];

// Domain info
define('DOMAINURL', 'http://'.$_SERVER['HTTP_HOST'].'/');
define('DOMAIN', $domain);
$domains = new DomainsController('Domains', 'domain', 'getDomains');
$domain = $domains->getDomains(NULL, $domain);
var_dump($domain);
#define('BRAND', $test);

// Debug Info
if (ERROR_LEVEL == 10
  && empty($_REQUEST['m'])) {
  var_dump($_REQUEST, $domain, $url, BRAND, DOMAINURL);
}

// Handle page request via Bootstrap
require_once (ROOT . DS . 'library' . DS . 'bootstrap.class.php');
new Bootstrap($url);