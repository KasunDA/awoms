<?php
// Security check: Deny direct file access; must be loaded through index getfile
if (count(get_included_files()) == 1) {
    header('Location: index.php');
    die('403');
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
$cartPrivateSettingsFile = "../../../kcart/cart_settings.inc.php";
// This makes available: $Brand/$brand, $Store/$store, $Cart/$cart
require_once($cartPrivateSettingsFile);
\Errors::debugLogger(PHP_EOL . '***** New (Cart) Page Load (' . $_SERVER['REQUEST_URI'] . ') *****', 1, true);
\Errors::debugLogger(PHP_EOL . serialize($_POST) . PHP_EOL . '*****' . PHP_EOL, 8);

// Load cart class and session data
echo str_replace("/home/dirt/Projects/AWOMS","",__FILE__).':'.__LINE__.'@'.time().'=Attempting to init AWOMS cart<BR/>';
$cart                    = new killerCart\KillerCart(CART_ID);

$auth                    = new killerCart\Auth();

if (empty($_REQUEST['customerID'])) {
    \Errors::debugLogger('['.__FILE__.':'.__LINE__.'] CustomerID is Empty. sessionName = Customer', 1, true);
    $sessionName = cartCodeNamespace.'Customer';
} else {
    \Errors::debugLogger('['.__FILE__.':'.__LINE__.'] CustomerID is: '.$_REQUEST['customerID'].'. sessionName = Admin', 1, true);
    $sessionName = cartCodeNamespace.'Admin';
}
echo str_replace("/home/dirt/Projects/AWOMS","",__FILE__).':'.__LINE__.'@'.time().'=SessionName='.$sessionName.':<BR/>';
// Stop loading rest if in ajax mode or mini-view mode
if ((!empty($_REQUEST['m']) && $_REQUEST['m'] == 'ajax') || (!empty($isCartMini))) {return;}
/***** END CART CODE *****/

// Ajax = dont regen session ID or you'll be 'logged out'
/*
if (!empty($_REQUEST['m']) && $_REQUEST['m'] == 'ajax') {
    $ajax = TRUE;
} else {
    $ajax = FALSE;
}
$auth->startSession(cartCodeNamespace.'Admin', $ajax);
*/

// Purge COOKIE from REQUEST
$_REQUEST                = array_merge($_GET, $_POST);
//
// Admin is logged in 
// 
if (!empty($_SESSION['userID'])) {
    \Errors::debugLogger('You are logged in', 10);
    //
    // ACL Assignments
    //
    $globalACL   = array('read'  => 0, 'write' => 0);
    $cartACL    = array('read'  => 0, 'write' => 0);
    $billingACL  = array('read'  => 0, 'write' => 0);
    $shippingACL = array('read'  => 0, 'write' => 0);
    // Shipping
    if ($_SESSION['groupName'] == 'Shipping'
            || $_SESSION['groupName'] == 'Global Administrator'
            || $_SESSION['groupName'] == 'Cart Administrator'
    ) {
        $shippingACL = array('read'  => 1, 'write' => 1);
    }
    // Accounting
    if ($_SESSION['groupName'] == 'Accounting'
            || $_SESSION['groupName'] == 'Global Administrator'
            || $_SESSION['groupName'] == 'Cart Administrator'
    ) {
        $billingACL = array('read'  => 1, 'write' => 1);
    }
    // Store Admins
    if ($_SESSION['groupName'] == 'Cart Administrator') {
        $cartACL = array('read'  => 1, 'write' => 1);
    }
    // Global Admins
    if ($_SESSION['groupName'] == 'Global Administrator') {
        $globalACL = array('read'  => 1, 'write' => 1);
    }

    //
    // Load Customer Data if impersonating customer
    //
    if (!empty($_SESSION['customerID'])) {
        $user                   = new killerCart\User();
        $u                      = $user->getUserInfo($_SESSION['userID']);
        $_SESSION['cartID']    = $u['cartID'];
        $_SESSION['cartName']  = $u['cartName'];
        $_SESSION['cartTheme'] = $u['cartTheme'];
        $_SESSION['userID']     = $u['userID'];
        $_SESSION['username']   = $u['username'];
        $_SESSION['groupID']    = $u['groupID'];
        $_SESSION['groupName']  = $u['groupName'];
        unset($_SESSION['customerID']);
    }
} else {
    \Errors::debugLogger('You are NOT logged in', 10);
}
// End if ajax call
if (!empty($_REQUEST['m']) && $_REQUEST['m'] == 'ajax') {
    \Errors::debugLogger('Ending ajax call', 10);
    return;
}
/***** END CART CODE *****/

//
// Login needed
//
if (empty($_SESSION['userID'])) {

    $_SESSION['cartTheme'] = 'default';

    if (!empty($_POST['a']) && $_POST['a'] == 'login') {
        if (empty($_POST['username']) || empty($_POST['passphrase'])) {
            trigger_error('Missing parameters.', E_USER_ERROR);
            return false;
        }
        $s    = new killerCart\Sanitize();
        $args = array('username'   => FILTER_SANITIZE_SPECIAL_CHARS,
            'passphrase' => FILTER_SANITIZE_SPECIAL_CHARS);
        if (!$san  = $s->filterArray(INPUT_POST, $args)) {
            trigger_error('Invalid parameters.', E_USER_ERROR);
            return false;
        }
        //$e   = new \Errors();
        $User = new User();
        $u = $User->ValidateLogin($san['username'], $san['passphrase']);
        if ($u === FALSE)
        {
            $fail_msg = "Login failed! Caps Lock? Typo? Try again or click 'Reset Password' to get a new one.";
        } else {
            // Setting session variables
            //$_SESSION['cartID']    = $u['cartID'];
            //$_SESSION['cartName']  = $u['cartName'];
            //$_SESSION['cartTheme'] = $u['cartTheme'];
            $_SESSION['userID']     = $u['userID'];
            $_SESSION['username']   = $san['username'];
            $_SESSION['groupID']    = $u['usergroupID'];
            $_SESSION['groupName']  = $u['usergroup']['usergroupName'];
            /*
            // Save unprotected private key in session for reading of encrypted data throughout session
            //$_SESSION['unprotPrivKey'] = $this->getUnprotectedPrivateKey($this->getCartUsersProtectedPrivateKey($_SESSION['userID']),
                                                                                                                 $password);
            // If user has no keypair, call makeKeys to handle
            //if (empty($_SESSION['unprotPrivKey'])) {
                //\Errors::debugLogger(__METHOD__ . ': Generating initial cart and user encryption keys on first login', 100);
                //$this->makeCartUserKeys($_SESSION['cartID'], $_SESSION['userID'], $password);
            //}
            */
            
            //header('Location: ' . $_SERVER['REQUEST_URI']);
        }

        /*
        if (!$auth->checkCartUserLogin($san['username'], $san['passphrase'])) {
            $e->dbLogger('Failed login for '.$san['username'], $_SESSION['cartID'], 'Audit', __FILE__, __LINE__);
            $fail_msg = "Login failed! Caps Lock? Typo? Try again or click 'Reset Password' to get a new one.";
        } else {
            $e->dbLogger('Successful login for '.$san['username'], $_SESSION['cartID'], 'Audit', __FILE__, __LINE__);
            header('Location: ' . $_SERVER['REQUEST_URI']);
        }
        */
    }

} else {
    //
    // Admin is logged in 
    // 

    //
    // Logout
    //
    if (!empty($_REQUEST['a']) && $_REQUEST['a'] == 'logout') {
        if (isset($_SERVER['HTTP_COOKIE'])) {
            $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
            foreach ($cookies as $cookie) {
                $parts = explode('=', $cookie);
                $name  = trim($parts[0]);
                if ($name != 'killerCartAdmin') {
                    continue;
                }
                setcookie($name, '', time() - 1000);
                setcookie($name, '', time() - 1000, '/');
            }
        }
        session_destroy();
        header('Location: ' . cartPublicUrl . 'admin');
    }
}

//
// Header Template
//
include(cartPrivateDir . 'templates/' . $_SESSION['cartTheme'] . '/admin/admin_header.phtml');