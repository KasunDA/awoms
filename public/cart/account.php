<?php
// Security check: Deny direct file access; must be loaded through index getfile
if (count(get_included_files()) == 1) {
    header("Location: index.php");
    die("403");
}
?>
<?php
// Customer Account Login
if (empty($_SESSION['customerID']) || !empty($fail_msg)) {
    \Errors::debugLogger('['.__FILE__.':'.__LINE__.'] Session[customerID] is empty or fail_msg');
    ?>
    <div class='hero-unit'>
        <h1>Welcome!&nbsp;<small>Please login to continue</small></h1>
        <hr />

        <form id='frm_customer_login' class='form-horizontal' method='POST'>
            <div class='control-group'>
                <label class='control-label' for='inpUsername'>Username</label>
                <div class='controls'>
                    <input type='text' id='username' name='username' placeholder='Username'>
                </div>
            </div>
            <div class='control-group'>
                <label class='control-label' for='password'>Passphrase</label>
                <div class='controls'>
                    <input type='password' id='passphrase' name='passphrase' placeholder='Passphrase'>
                </div>
            </div>
            <div class='control-group'>
                <div class='controls'>
                    <button name='a' id='a' value='login' type='submit' class='btn btn-primary btn-large'>Sign in &raquo;</button>
                </div>
            </div>
            <?php
            if (!empty($fail_msg)) {
                echo "<div class='alert alert-block alert-danger'>" . $fail_msg . "</div>";
            }
            ?>
        </form>

    </div>
    <?php
    //require('footer.php');
    die();
}

//
// No account page to show yet
//
if ($_SESSION['customerID'] == 'NEW') {
    \Errors::debugLogger('['.__FILE__.':'.__LINE__.'] Session[customerID] is NEW');
    include(cartPrivateDir . 'templates/' . $cart->session['cartTheme'] . '/cart/cart_view.inc.phtml');
    require('footer.php');
    die();
}

//
// Customer Authenticated
//
$customer = new killerCart\Customer();
$c        = $customer->getCustomerInfo($_SESSION['customerID']);
\Errors::debugLogger('['.__FILE__.':'.__LINE__.'] Session[customerID] is VALID');
include(cartPrivateDir . 'templates/' . $cart->session['cartTheme'] . '/customer/customer_home.inc.phtml');
?>