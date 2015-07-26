<?php
// Security check: Deny direct file access; must be loaded through index getfile
if (count(get_included_files()) == 1) {
    header("Location: index.php");
    die("403");
}

/***** BEGIN CART CODE *****/
/* ! IMPORTANT: DO NOT CHANGE THE BEGINNING OR ENDING TAGS OF THIS 'CART CODE' SECTION ! */
/*****
 * Set $cartID to your cart ID
 * Set $cartPrivateSettingsFile to the FULL PATH to the 'cart_settings.inc.php' file
 * Note: This file should NOT be in the public web root for security
 * Example: "/var/www/vhosts/cart.com/private/cart/cart_settings.inc.php"
 * Example: "C:\wamp\www\cart.com\private\cart\cart_settings.inc.php";
 *****/
//$cartPrivateSettingsFile = "E:/Projects/GPFC/cart/cart_settings.inc.php";
$cartPrivateSettingsFile = "../../kcart/cart_settings.inc.php";
// This makes available: $Brand/$brand, $Store/$store, $Cart/$cart
require_once($cartPrivateSettingsFile);
\Errors::debugLogger(PHP_EOL . '***** New (Cart) Page Load (' . $_SERVER['REQUEST_URI'] . ') *****', 1, true);
\Errors::debugLogger(PHP_EOL . serialize($_POST) . PHP_EOL . '*****' . PHP_EOL, 8);

// Load cart class and session data
echo str_replace("/home/dirt/Projects/AWOMS","",__FILE__).':'.__LINE__.'@'.time().'=Attempting to init AWOMS cart<BR/>';
$cart                    = new killerCart\KillerCart(CART_ID);
// Authentication and Authorization
$auth                    = new killerCart\Auth();

if (empty($_REQUEST['customerID'])) {
    \Errors::debugLogger('['.__FILE__.':'.__LINE__.'] CustomerID is Empty. sessionName = Customer', 1, true);
    $sessionName = cartCodeNamespace.'Customer';
} else {
    \Errors::debugLogger('['.__FILE__.':'.__LINE__.'] CustomerID is: '.$_REQUEST['customerID'].'. sessionName = Admin', 1, true);
    $sessionName = cartCodeNamespace.'Admin';
}
echo str_replace("/home/dirt/Projects/AWOMS","",__FILE__).':'.__LINE__.'@'.time().'=SessionName='.$sessionName.':<BR/>';

//
// BEGIN CODE SPECIFIC TO EXISTING USER LOGIN EXCHANGE
// (Allow use of existing logins exchanged to cart customers accounts)
//
/*
if ($_SERVER['HTTP_HOST'] == "goinpostal.com")
{
// This must match the existing session cookie name
$existingSessionName = "PHPSESSID"; // <-- When removing GP code this reference in the Logout section below needs to be removed as well
// Customer Session
if ($sessionName == 'killerCartCustomer') {
    // Not logged into Cart
    if (empty($_SESSION['customerID'])) {
        // No cookie -> redirect to existing login
        if (empty($_COOKIE[$existingSessionName])) {
            $existingAuthenticated = FALSE;
        } else {
            // Cookie exists, Check for unconsumed user exchange sessions
            $ux = $auth->getUserExchange($_COOKIE[$existingSessionName]);

            if (OPMODE == 'DEV') {
                $ux                   = array();
                $ux['existingUser']   = 'zephyrhills';
                $ux['sessionName']    = $existingSessionName;
                $ux['sessionValue']   = $_COOKIE[$existingSessionName];
                $ux['authenticated']  = 1;
                $ux['sessionExpires'] = NULL;
            }

            // No unconsumed sessions available, will redirect to login
            if ($ux === FALSE) {
                $existingAuthenticated = FALSE;
            } else {
                // Unconsumed session available, consume/initlogin! (<.....*
                $existingAuthenticated  = TRUE;
                $customer               = new killerCart\Customer();
                $_SESSION['customerID'] = $customer->getCustomerIDByUsername($ux['existingUser']);

                // Owners login authenticated, but no cart customer ID for this user yet...
                if (empty($_SESSION['customerID'])) {
                    $dbCustomerID                  = NULL;
                    $_SESSION['customerID']        = 'NEW';
                    $_SESSION['cInfo']['username'] = $ux['existingUser'];
                } else {
                    $dbCustomerID      = $_SESSION['customerID'];
                    $_SESSION['cInfo'] = $customer->getCustomerInfo($_SESSION['customerID']);
                }
                // Consume exchange session
                $auth->updateUserExchange($ux['sessionName'], $ux['sessionValue'], $ux['authenticated'], $ux['existingUser'],
                                          $dbCustomerID, $ux['sessionExpires'], TRUE);
            }
        }
    } else {
        // Is logged In
        $existingAuthenticated = TRUE;

        // Load Customer Info
        if (empty($_SESSION['cInfo']['firstName']) && $_SESSION['customerID'] != 'NEW') {
            $customer          = new killerCart\Customer();
            $_SESSION['cInfo'] = $customer->getCustomerInfo($_SESSION['customerID']);
        }
    }
    // Redirect to Owners Login
    if ($existingAuthenticated === FALSE) {
        $devLoc = strtolower(OPMODE);
        if ($devLoc == 'prod') {
            $devLoc = 'cart';
        }
        header("Location: https://www.goinpostal.com/owners/login.php?returnUrl=".$devLoc);
        exit;
    }
}
}
 * 
 */
//
// END CODE SPECIFIC TO GOINPOSTAL.COM OWNERS STORE
//

// Admin Impersonation
if ($sessionName == 'killerCartAdmin') {
    
    \Errors::debugLogger('['.__FILE__.':'.__LINE__.'] killerCartAdmin', 1, true);
    
    // ACL Check
    if (!empty($_SESSION['groupID']) && $_SESSION['groupID'] <= 2
    ) {
        
        \Errors::debugLogger('['.__FILE__.':'.__LINE__.'] Load Customer Data', 1, true);
        
        // Load Customer Data into Session (one-time only)
        if (empty($_SESSION['customerID']) || ($_SESSION['customerID'] != $_REQUEST['customerID'])) {
            $s                      = new killerCart\Sanitize();
            $_SESSION['customerID'] = $s->filterSingle($_REQUEST['customerID'], FILTER_SANITIZE_NUMBER_INT);
            $customer               = new killerCart\Customer();
            $_SESSION['cInfo']      = $customer->getCustomerInfo($_SESSION['customerID']);
        }
    } else {
        
        \Errors::debugLogger('['.__FILE__.':'.__LINE__.'] Security catch', 1, true);
        
        trigger_error('Security catch.', E_USER_ERROR);
        return false;
    }
}

// Session cart info
if (empty($_SESSION['cartID'])) {
    $cart->getCartInfo($cartID);
    $_SESSION['cartID']    = $cart->session['cartID'];
    $_SESSION['cartName']  = $cart->session['cartName'];
    $_SESSION['cartTheme'] = $cart->session['cartTheme'];
}

// Stop loading rest if in ajax mode or mini-view mode
if (
        (!empty($_REQUEST['m']) && $_REQUEST['m'] == 'ajax') ||
        (!empty($isCartMini))
) {
    return;
}
/***** END CART CODE *****/

//
// (SEO) Page Title
//
//$pageTitle = $_SESSION['cartName'];
//$metaKeywords = "cheapledopensigns, cheap, led, open, signs";
//$metaDescription = "Cheap LED Open Signs";

//
// Login / Logout
//
if (!empty($_REQUEST['p']) && $_REQUEST['p'] == 'account') {
    // Logout
    if (!empty($_REQUEST['a']) && $_REQUEST['a'] == 'logout') {
        if (isset($_SERVER['HTTP_COOKIE'])) {
            $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
            foreach ($cookies as $cookie) {
                $parts = explode('=', $cookie);
                $name  = trim($parts[0]);
                // Only delete our cookies
                
                // CODE SPECIFIC TO EXISTING USER LOGIN EXCHANGE
                if ($_SERVER['HTTP_HOST'] == "goinpostal.com")
                {
                    if (!in_array($name, array('killerCartCustomer', $existingSessionName))) {
                        continue;
                    }
                }
                else
                {
                    if (!in_array($name, array('killerCartCustomer'))) {
                        continue;
                    }
                }
                
                setcookie($name, '', time() - 1000);
                setcookie($name, '', time() - 1000, '/');
            }
        }
        session_destroy();
        
        \Errors::debugLogger('['.__FILE__.':'.__LINE__.'] RESET Send to Account', 1, true);
        
        header('Location: ' . cartPublicUrl . '?p=account');
        exit(0);
    }

    // Login
    if (empty($_SESSION['customerID']) || $_SESSION['customerID'] == 'NEW') {

        if (!empty($_POST['a']) && $_POST['a'] == 'login') {
            if (empty($_POST['username']) || empty($_POST['passphrase'])) {
                trigger_error('1006 - Invalid login parameters', E_USER_ERROR);
                return false;
            }
            if (empty($s)) {
                $s = new killerCart\Sanitize();
            }
            $args = array('username'   => FILTER_SANITIZE_SPECIAL_CHARS,
                'passphrase' => FILTER_SANITIZE_SPECIAL_CHARS);
            if (!$san  = $s->filterArray(INPUT_POST, $args)) {
                trigger_error('Invalid parameters.', E_USER_ERROR);
                return false;
            }
            if (!$auth->checkCustomerUserLogin($san['username'], $san['passphrase'])) {
                $fail_msg = "Login failed! Caps Lock? Typo? Try again or click 'Reset Password' to get a new one.";
            } else {

                // Returning customer logged in trying to checkout
                if (!empty($_REQUEST['r']) && $_REQUEST['r'] == 'checkout'
                ) {
                    header('Location: ' . cartPublicUrl . '?p=checkout');
                    exit(0);
                } else {
                    header('Location: ' . $_SERVER['REQUEST_URI']);
                    exit(0);
                }
            }
        }
    }
}

//
// Selected Page: Load data for header/breadcrumbs template
//
if (empty($_REQUEST['p']) || $_REQUEST['p'] == 'cart') {
    //
    // View Cart
    //
    echo str_replace("/home/dirt/Projects/AWOMS","",__FILE__).':'.__LINE__.'@'.time().'=View cart<BR/>';
} elseif ($_REQUEST['p'] == 'category') {

    //
    // Category Info
    //

    $category = new killerCart\Product_Category();
    
    // Sanitize & Validate Form
    $validated = FALSE;
    
    // If a category was chosen or there is only 1 category, go to that category
    $cat_count = $category->getCategoryIDs('ACTIVE', $_SESSION['cartID'], 'pc.categoryName');
    if (!empty($_REQUEST['categoryID'])
        || count($cat_count) == 1)
    {
        if (count($cat_count) == 1)
        {
            $validated = TRUE;
            $c = $category->getCategoryInfo($cat_count[0]['categoryID']);
        }
        else
        {
            $s = new killerCart\Sanitize;
            if ($s->filterSingle($_REQUEST['categoryID'], FILTER_SANITIZE_NUMBER_INT)) {
                $validated = TRUE;
                $c = $category->getCategoryInfo($_REQUEST['categoryID']);
            }
        }
        $pageTitle = $_SESSION['cartName'].' - '.$c['categoryName'];
        if (!empty($c['categoryDescriptionPublic']))
        {
            $metaDescription = $c['categoryDescriptionPublic'];
        }
    }
    else
    {
        // All categories
        $validated = TRUE;
    }
    if ($validated === FALSE) {
        trigger_error('Validation failed.', E_USER_ERROR);
        return false;
    }
} elseif ($_REQUEST['p'] == 'product') {

    //
    // Product Info
    //

    // Sanitize & Validate Form
    $validated = FALSE;
    if (isset($_REQUEST['productID'])) {
        $s = new killerCart\Sanitize;
        if ($s->filterSingle($_REQUEST['productID'], FILTER_SANITIZE_NUMBER_INT)) {
            $validated = TRUE;
        }
    }
    if ($validated === FALSE) {
        trigger_error('Validation failed.', E_USER_ERROR);
        return false;
    }

    $product  = new killerCart\Product();
    $p        = $product->getProductInfo($_REQUEST['productID']);
    $category = new killerCart\Product_Category();
    $c        = $category->getCategoryInfo($p['categoryID']);
    
    $pageTitle = $_SESSION['cartName'].' - '.$p['productName'];
    if (!empty($p['productDescriptionPublic']))
    {
        $metaDescription = strip_tags(str_replace("\"", "'", $p['productDescriptionPublic']));
    }
}

//
// Cart Product Count
//
$cartItemQty = 0;
if (!empty($cart->products)) {
    foreach ($cart->products as $cp) {
        $cartItemQty += $cp['qty'];
    }
}

//
// Header Template
//
include(cartPrivateDir . 'templates/' . $cart->session['cartTheme'] . '/header.phtml');
?>