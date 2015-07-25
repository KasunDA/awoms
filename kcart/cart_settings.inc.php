<?php
/***** DO NOT MODIFY THIS LINE: BEGIN SETTINGS *****/
// Cart Version
if (!defined('cartVersion')) {
    define('cartName', 'gpCart');
    define('cartVersion', 'v02.00.01');
    define('cartFolder', 'kcart'); // name of private cart folder
}

// Define Operation Mode:
// PROD - Production, STAGE - Stage, DEV - Dev, SAND - Sandbox
if (!defined("OPMODE")) { define('OPMODE', 'DEV'); }
// Database Server
if (!defined("HOST")) { define('HOST', 'dev.hutno8.com'); }
// Database Server Port
if (!defined("PORT")) { define('PORT', '3306'); }
// Database Username
if (!defined("USER")) { define('USER', 'GPFC_Cart'); }
// Database Password
if (!defined("PASS")) { define('PASS', 'F7!unm75@mh'); }
// Database Name
if (!defined("DBNAME")) { define('DBNAME', 'GPFC'); }
// Database Timeout
if (!defined("DBTIMEOUT")) { define('DBTIMEOUT', '15'); }
    
/********************/
/* AWOMS Integration */
if (!defined("DS")) { define('DS', DIRECTORY_SEPARATOR); }
if (!defined("ROOT")) { define('ROOT', dirname(dirname(__FILE__))); }
require_once (ROOT.DS."config".DS."config.php");
$Domain = new Domain();
$domain = $Domain->getSingle(array('domainName' => $_SERVER['HTTP_HOST']));
if (empty($domain))
{
    trigger_error("1001 Unknown domain name");
    exit(0);
}

if (!defined("CART_ID")) {
    $domainID = $domain['domainID'];
    $brandID = $domain['brandID'];
    $storeID = $domain['storeID'];
    // Is this a specific Store ID's website/cart?
    if (!empty($storeID))
    {
        // Store has cart?
        $Store = new Store();
        $store = $Store->getSingle(array('storeID' => $storeID));
        $cartID = $store['cartID'];
    }
    // or... Is this just the main Brand ID's website/cart (Owner's store?)
    if (empty($cartID) && !empty($brandID))
    {
        // Brand has cart?
        $Brand = new Brand();
        $brand = $Brand->getSingle(array('brandID' => $brandID));
        $cartID = $brand['cartID'];
    }
    if (empty($cartID))
    {
        trigger_error("1002 Unknown cart");
        exit(0);
    }
    $Cart = new Cart();
    $cart = $Cart->getSingle(array('cartID' => $cartID));
    define('CART_ID', $cartID);
}
/********************/
    
// Public Cart Folder URL (full HTTPS URL AND trailing slash)
if (!defined('cartPublicUrl')) {
    if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) {
        define('cartPublicUrl', 'https://'.$_SERVER['HTTP_HOST'].'/cart/');
    } else {
        define('cartPublicUrl', 'http://'.$_SERVER['HTTP_HOST'].'/cart/');
    }
}
// Site Home URL (full HTTP URL AND trailing slash)
if (!defined('sitePublicUrl')) {
    define('sitePublicUrl', 'http://'.$_SERVER['HTTP_HOST'].'/');
}
/***** Private Cart Folder Path
 * Set this to the FULL PATH to the directory holding all PRIVATE cart files INCLUDING TRAILING SLASH
 * Example: '/var/www/vhosts/cart.com/private/cart/'
 * Example: 'C:\wamp\www\devcart.com\private\cart\\' <-- note the end slash must be a double slash if using backslash/windows
 */
if (!defined('cartPrivateDir')) {
    define('cartPrivateDir', ROOT.DS.cartFolder.DS);
}
if (!defined('cartImagesDir')) {
    define('cartImagesDir', cartPrivateDir.'images'.DS);
}
if (!defined('cartLogDir')) {
    define('cartLogDir', cartPrivateDir.'logs'.DS);
}
if (!defined('cartAdminDir')) {
    define('cartAdminDir', ROOT.DS.'/public/cart/admin/');
}
/***** Debug Mode
 * Set $cartDebug to true when in Debug/Dev mode to show detailed error messages
 * Set $cartDebug to false when in Production Mode to show friendly error messages
 *****/
$cartDebug = true;
/***** Debug Level
 * Set $cartDebugLevel to the level of details to record, higher levels include all child levels (e.g. 5 includes 4,3,2,1)
 * 10 (Everything!) - Utilities(), Sanitize() - (PCI Violation!) Will cause a lot of logging *Be sure to only enable temporarily and delete logs afterwards as they contain sensitive info*
 * 9 (Sensitive!) - Auth() - (PCI Violation!) Encryption/decryption/authentication raw information *Be sure to only enable temporarily and delete logs afterwards as they contain sensitive info*
 * 8 (Form data) - func_get_args() - (PCI Violation!) All raw form/post data and function arguments *Be sure to only enable temporarily and delete logs afterwards as they contain sensitive info*
 * 7 - 
 * 6 - 
 * 5 (Debug) - __METHOD__ / __FILE__ / __LINE__ PCI Safe debugging level
 * 4 - 
 * 3 - 
 * 2 - 
 * 1 (Info) - If msg lvl undefined assumes level 1
 * 0 (None) - Disabled (Unless forced)
 *****/
$cartDebugLevel = 8;

    
/***** DO NOT MODIFY BELOW THIS LINE *****/
//if ($cartDebug) {
//    ini_set('display_errors', 'On');
//    ini_set('display_startup_errors', 'On');
//    ini_set('html_errors', 'On');
//    error_reporting(-1);
//    if (!defined('cartDebug')) {
//        define('cartDebug', true);
//    }
//    if (!defined('cartDebugLevel')) {
//        define('cartDebugLevel', $cartDebugLevel);
//    }
//} else {
//    ini_set('display_errors', 'Off');
//    ini_set('display_startup_errors', 'Off');
//    ini_set('html_errors', 'On');
//    error_reporting(-1);
//    if (!defined('cartDebug')) {
//        define('cartDebug', false);
//    }
//    if (!defined('cartDebugLevel')) {
//        define('cartDebugLevel', $cartDebugLevel);
//    }
//}

//
// Define global variables
// 
// Define namespace to avoid colliding with existing php class names
if (!defined('cartCodeNamespace')) {
    define('cartCodeNamespace', 'killerCart');
}
// Used to prevent colliding with existing site css
if (!defined('cartCssClassPrefix')) {
    define('cartCssClassPrefix', cartCodeNamespace . 'Style');
}
// Related directories
if (!defined('cartClassesDir')) {
    define('cartClassesDir', cartPrivateDir . 'lib/');
}

//
// Load required starter files
// 
// Load and register error handler
//require_once (cartClassesDir . 'error.inc.php');
//set_error_handler(array('\Errors', 'captureNormal'));
//set_exception_handler(array('\Errors', 'captureException'));
//register_shutdown_function(array('\Errors', 'captureShutdown'));

// Load database connector
//require_once (cartClassesDir . 'database.inc.php');

// Register autoloader
spl_autoload_register(function ($className) {
    // Classname passed must start with namespace e.g. killerCart\Class
    $className = explode('\\', $className);
    if (count($className) <= 1) {
        trigger_error('1003 - Unable to load class!', E_USER_ERROR);
        return false;
    }
    $className = $className[1];

    if (!in_array($className, get_declared_classes())) {
        if ($className == "PDO") {
            return;
        }
        $filePath = cartClassesDir . strtolower($className) . '.inc.php';
        if (!is_file($filePath)) {
            trigger_error('1004 - Unable to load class file! ' . $filePath . ')', E_USER_ERROR);
            return false;
        }
        require_once($filePath);
    }
});

/***** DO NOT MODIFY THIS LINE: END SETTINGS *****/
?>