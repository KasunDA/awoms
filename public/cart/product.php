<?php
// Security check: Deny direct file access; must be loaded through index getfile
if (count(get_included_files()) == 1) {
    header("Location: index.php");
    die("403");
}
?>

<?php
include(cartPrivateDir . 'templates/' . $cart->session['cartTheme'] . '/product/product_view.inc.phtml');
?>