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

// Domain (from .htaccess)
if (filter_has_var(INPUT_GET, 'domain')) {
  $domain = $_GET['domain'];
} else {
  $domain = $_SERVER['HTTP_HOST'];
}
// DEV
$domain = str_replace('dev.', '', $domain);

// URL (from .htaccess)
if (filter_has_var(INPUT_GET, 'url')) {
  $url = $_GET['url'];
} else {
  $url = 'home';
}

// Domain info
$domains = new DomainsController('Domains', 'Domain', 'getDomains');
$domainInfo = $domains->getDomains(NULL, $domain);
$deny = TRUE;
if (!empty($domainInfo)) {
  if ((int)$domainInfo['domainActive'] === 1) {
    $deny = FALSE;
    $brandID = $domainInfo['brandID'];
    define('DOMAINURL', 'http://'.$_SERVER['HTTP_HOST'].'/');
    define('DOMAIN', $domainInfo['domainName']);
  }
}

// Stop if domain not found
if ($deny === TRUE) {
  trigger_error('Domain not found: '.$domain, E_USER_ERROR);
  die();
}

// Brand info
$brands = new BrandsController('Brands', 'Brand', 'getBrands');
$brandInfo = $brands->getBrands($brandID);
$deny = TRUE;
if (!empty($brandInfo)) {
  if ((int)$brandInfo['brandActive'] === 1) {
    $deny = FALSE;
    define('BRAND', $brandInfo['brandName']);
  }
}

// Stop if brand not found
if ($deny === TRUE) {
  trigger_error('Brand not found: '.$brandID, E_USER_ERROR);
  die();
}

// Debug Info
if (ERROR_LEVEL == 10
  && empty($_REQUEST['m'])) {
  var_dump($_REQUEST, $domain, $url, BRAND, DOMAINURL);
}

// Handle page request via Bootstrap
require_once (ROOT . DS . 'library' . DS . 'bootstrap.class.php');
new Bootstrap($url);