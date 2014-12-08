<?php

// Security check: Deny direct file access; must be loaded through index getfile
if (count(get_included_files()) == 1) {
    header("Location: index.php");
    die("403");
}
?>
<?php

// If empty cart, don't show checkout - just cart view page
if (!isset($cart->products) || count($cart->products) == 0) {
    include(cartPrivateDir . 'templates/' . $cart->getCartTheme() . '/cart/cart_view.inc.phtml');
    return false;
}

// Cart total (check for free-checkout w/ no ship/pm forms)
$ct = 0;
foreach ($cart->products as $cp) {
    $ct += ($cp['price'] * $cp['qty']);
    // Options Subtotal
    if (!empty($cp['options'])) {
        $total_cart_options = 0;
        foreach ($cp['options'] as $tpo) {
            $optionID = $tpo['optionID'];
            foreach ($cp['options'][$optionID]['choices'] as $tpoc) {
                $price = $tpoc['price'];
                $total_cart_options += ($cp['qty'] * $price);
            }
        }
        $ct += $total_cart_options;
    }
}

// Is logged in (Customer Checkout)
if (!empty($_SESSION['customerID']) && $_SESSION['customerID'] != 'NEW') {

    if ($ct > 0) {

        // Normal checkout w/ shipping/payment method
        include(cartPrivateDir . 'templates/' . $cart->session['cartTheme'] . '/checkout/checkout_customer.inc.phtml');
    } else {

        // Free checkout
        include(cartPrivateDir . 'templates/' . $cart->session['cartTheme'] . '/checkout/checkout_customer_free.inc.phtml');
    }

// Not logged in (New Checkout)
} else {

    include(cartPrivateDir . 'templates/' . $cart->session['cartTheme'] . '/checkout/checkout_new.inc.phtml');
}

// Shared JavaScript for all checkout processes
include(cartPrivateDir . 'templates/' . $cart->session['cartTheme'] . '/checkout/checkout_validation.inc.phtml');
?>