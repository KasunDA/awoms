<?php
//
// Load Cart Header (unless in mini mode)
//
if (empty($cart_mini_mode)) {
    require('header.php');
    $fileloc_prefix = "";
} else {
    $fileloc_prefix = "../"; // Sets file include path prefix to up a directory when in mini-mode (set appropriately depending on location of mini-cart to cart)
}

//
// Load selected action required file
//
if (isset($_REQUEST['p']) && $_REQUEST['p'] != 'index') {
    $page = $_REQUEST['p'];
} else {
    $page = 'home';
    if ($cart->getCartTheme() == "goinpostal")
    {
        $page = 'cart'; // Defaults to cart view
    }
}
if (is_file($fileloc_prefix . $page . '.php')) { // Is valid file
    include($fileloc_prefix . $page . '.php'); // Show selected page
} else { // 404
    ?>
    <div class="container">
        <h1>Not found <span>:(</span></h1>
        <p>Sorry, but the page you were trying to view does not exist.</p>
        <p>It looks like this was the result of either:</p>
        <ul>
            <li>a mistyped address</li>
            <li>an out-of-date link</li>
        </ul>
        <p>Please <a href="javascript:history.go(-1)">go back</a> and try again.</p>
        <script>
            var GOOG_FIXURL_LANG = (navigator.language || '').slice(0, 2), GOOG_FIXURL_SITE = location.host;
        </script>
        <script src="http://linkhelp.clients.google.com/tbproxy/lh/wm/fixurl.js"></script>
    </div>
    <?php
}

//
// Load Cart Footer (unless in mini mode)
//
if (empty($cart_mini_mode)) {
    require('footer.php');
}
?>