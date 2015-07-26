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
$cart                    = new killerCart\KillerCart(CART_ID);
$auth                    = new killerCart\Auth();
if (empty($_REQUEST['customerID'])) {
    \Errors::debugLogger('['.__FILE__.':'.__LINE__.'] CustomerID is Empty. sessionName = Customer', 1, true);
    $sessionName = cartCodeNamespace.'Customer';
} else {
    \Errors::debugLogger('['.__FILE__.':'.__LINE__.'] CustomerID is: '.$_REQUEST['customerID'].'. sessionName = Admin', 1, true);
    $sessionName = cartCodeNamespace.'Admin';
}
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
if (!empty($_SESSION['user'])) {
    \Errors::debugLogger('You are logged in', 10);
    //
    // ACL Assignments (Role based)
    //
    // @TODO default to 0s
    $_SESSION['user']['ACL']['global']   = array('read'  => 1, 'write' => 1);
    $_SESSION['user']['ACL']['cart']    = array('read'  => 1, 'write' => 1);
    $_SESSION['user']['ACL']['billing']  = array('read'  => 1, 'write' => 1);
    $_SESSION['user']['ACL']['shipping'] = array('read'  => 1, 'write' => 1);

    Session::saveSessionToDB();

    /* @TODO
    // Shipping
    if ($_SESSION['user']['usergroupName'] == 'Shipping'
            || $_SESSION['user']['usergroupName'] == 'Global Administrator'
            || $_SESSION['user']['usergroupName'] == 'Cart Administrator'
    ) {
        $_SESSION['user']['ACL']['shipping'] = array('read'  => 1, 'write' => 1);
    }
    // Accounting
    if ($_SESSION['user']['usergroupName'] == 'Accounting'
            || $_SESSION['user']['usergroupName'] == 'Global Administrator'
            || $_SESSION['user']['usergroupName'] == 'Cart Administrator'
    ) {
        $_SESSION['user']['ACL']['billing'] = array('read'  => 1, 'write' => 1);
    }
    // Store Admins
    if ($_SESSION['user']['usergroupName'] == 'Cart Administrator') {
        $_SESSION['user']['ACL']['cart'] = array('read'  => 1, 'write' => 1);
    }
    // Global Admins
    if ($_SESSION['user']['usergroupName'] == 'Global Administrator') {
        $_SESSION['user']['ACL']['global'] = array('read'  => 1, 'write' => 1);
    }
    */

    //
    // Load Customer Data if impersonating customer
    //
    if (!empty($_SESSION['customerID'])) {
        $user                   = new killerCart\User();
        $u                      = $user->getUserInfo($_SESSION['user']['userID']);
        $_SESSION['cartID']    = $u['cartID'];
        $_SESSION['cartName']  = $u['cartName'];
        $_SESSION['cartTheme'] = $u['cartTheme'];
        $_SESSION['user']['userID']     = $u['userID'];
        $_SESSION['user']['userName']   = $u['username'];
        $_SESSION['user']['usergroup']['usergroupID']    = $u['groupID'];
        $_SESSION['user']['usergroupName']  = $u['groupName'];
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
// Login required
//
if (empty($_SESSION['user'])) {
    if (empty($_SESSION['cartTheme']))
    {
        $_SESSION['cartTheme'] = 'default';
    }

    // Login posted
    if (!empty($_POST['a']) && $_POST['a'] == 'login') {
        // Validate and sanitize required params
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

        // Now, Validate Login attempt
        $e   = new \Errors();
        $User = new User();
        $u = $User->ValidateLogin($san['username'], $san['passphrase']);
        if ($u === FALSE)
        {
            $e->dbLogger('Failed login for '.$san['username'], $_SESSION['cartID'], 'Audit', __FILE__, __LINE__);
            $fail_msg = "Login failed! Caps Lock? Typo? Try again or click 'Reset Password' to get a new one.";
        } else {
            $e->dbLogger('Successful login for '.$san['username'], $_SESSION['cartID'], 'Audit', __FILE__, __LINE__);
            // Setting (missing) session variables
            //$_SESSION['cartID']    = $u['cartID'];
            //$_SESSION['cartName']  = $u['cartName'];
            //$_SESSION['cartTheme'] = $u['cartTheme'];
            $_SESSION['user']['userID']     = $u['userID'];
            $_SESSION['user']['userName']   = $san['username'];
            $_SESSION['user']['usergroup']  = $u['usergroup'];

            /*
            // Save unprotected private key in session for reading of encrypted data throughout session
            //$_SESSION['unprotPrivKey'] = $this->getUnprotectedPrivateKey($this->getCartUsersProtectedPrivateKey($_SESSION['user']['userID']),
                                                                                                                 $password);
            // If user has no keypair, call makeKeys to handle
            //if (empty($_SESSION['unprotPrivKey'])) {
                //\Errors::debugLogger(__METHOD__ . ': Generating initial cart and user encryption keys on first login', 100);
                //$this->makeCartUserKeys($_SESSION['cartID'], $_SESSION['user']['userID'], $password);
            //}
            */

            //reload page (to have new settings take effect)
            Session::saveSessionToDB();
            header('Location: ' . $_SERVER['REQUEST_URI']);
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