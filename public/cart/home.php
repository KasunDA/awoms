<?php

// Security check: Deny direct file access; must be loaded through index getfile
if (count(get_included_files()) == 1) {
    header("Location: index.php");
    die("Not allowed. ");
}
?>
<?php

include(cartPrivateDir . 'templates/' . $cart->session['cartTheme'] . '/storefront_home.inc.phtml');
?>