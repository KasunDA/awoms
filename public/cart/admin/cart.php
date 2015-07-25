<?php
// Security check: Deny direct file access; must be loaded through index getfile
if (count(get_included_files()) == 1) {
    header("Location: index.php");
    die("403");
}
?>
<?php
//
// ACL Check
//
if (empty($cartACL['read']) && empty($globalACL['read'])
) {
    die("Unauthorized Access (403)");
}

//
// BEGIN: Ajax call 
//
if (isset($_REQUEST['m'])
        && ($_REQUEST['m'] == 'ajax')) {

    // 
    // Product Images
    // 
    // Product Image (Remove)
    if (isset($_REQUEST['a'])
            && $_REQUEST['a'] == 'removeImage') {
        // Init Image
        $image      = new killerCart\Image();
        $image->setImageActive($_POST['cartID'], $_POST['imageID'], 0);
    }
    
    //
    // Storefront Carousel
    //
    if (isset($_REQUEST['qa'])
            && $_REQUEST['qa'] == 'storefront_carousel_slide')
    {
        $cartID   = $_REQUEST['cartID'];
        $slideNum = $_REQUEST['slideNum'];
        $newImageID = $_REQUEST['newImageID'];
        
        $cart     = new killerCart\KillerCart($cartID);
        $s = $cart->getCartInfo($cartID);
        $cart->updateCartCarouselSlide($cartID, $slideNum, $newImageID);
        echo "Saved Carousel Slide!";
        exit;
    }
    
    if (isset($_REQUEST['qa'])
            && $_REQUEST['qa'] == 'storefront_carousel')
    {
      if (!empty($_REQUEST['cartID']))
      {
        $cartID   = $_REQUEST['cartID'];
        
        $slide1ImageID = $_REQUEST['slide1ImageID'];
        $slide1Title = $_REQUEST['slide1Title'];
        $slide1Description = $_REQUEST['slide1Description'];
        $slide1URL = $_REQUEST['slide1URL'];
        
        $slide2ImageID = $_REQUEST['slide2ImageID'];
        $slide2Title = $_REQUEST['slide2Title'];
        $slide2Description = $_REQUEST['slide2Description'];
        $slide2URL = $_REQUEST['slide2URL'];
        
        $slide3ImageID = $_REQUEST['slide3ImageID'];
        $slide3Title = $_REQUEST['slide3Title'];
        $slide3Description = $_REQUEST['slide3Description'];
        $slide3URL = $_REQUEST['slide3URL'];
        
        $slide4ImageID = $_REQUEST['slide4ImageID'];
        $slide4Title = $_REQUEST['slide4Title'];
        $slide4Description = $_REQUEST['slide4Description'];
        $slide4URL = $_REQUEST['slide4URL'];
        
        $slide5ImageID = $_REQUEST['slide5ImageID'];
        $slide5Title = $_REQUEST['slide5Title'];
        $slide5Description = $_REQUEST['slide5Description'];
        $slide5URL = $_REQUEST['slide5URL'];
        
        $cartHomeDesc = $_REQUEST['homepageDescription'];
        
        $cart     = new killerCart\KillerCart($cartID);
        $s = $cart->getCartInfo($cartID);
        $cart->setCartCarousel($_POST['cartID'], $_POST['showCarousel'], $_POST['carouselInterval'],
                $slide1ImageID, $slide1Title, $slide1Description, $slide1URL,
                $slide2ImageID, $slide2Title, $slide2Description, $slide2URL,
                $slide3ImageID, $slide3Title, $slide3Description, $slide3URL,
                $slide4ImageID, $slide4Title, $slide4Description, $slide4URL,
                $slide5ImageID, $slide5Title, $slide5Description, $slide5URL, $cartHomeDesc);
        echo "Saved Cart Carousel!";
        exit;
      }
    }
    
    //
    // Save Cart
    //
    if (isset($_REQUEST['a'])
            && $_REQUEST['a'] == "edit_cart") {

        // Load selected cart info
        if (!empty($_REQUEST['cartID'])) {
            
            $cartID   = $_REQUEST['cartID'];
            $cart     = new killerCart\KillerCart($cartID);
            $s = $cart->getCartInfo($cartID);
        }

        if (empty($_REQUEST['ia'])) {

            // Step 2: Save Cart (Add or Edit)
            if (!empty($_POST['s']) && $_POST['s'] == 2) {

                // Success
                if ($cart->saveCart()) {

                    // Images
                    if (!empty($_FILES)) {
                        if (file_exists($_FILES['files']['tmp_name'][0]) && is_uploaded_file($_FILES['files']['tmp_name'][0])) {
                            killerCart\Util::handleFileUpload('files', 'image', $cartID);
                        }
                    }
                    ?>
                    <div class='alert alert-block alert-success'>
                        <button type='button' class='close' data-dismiss='alert'>&times;</button>
                        <h4>Success!</h4>
                        Cart saved successfully.
                        <ul>
                            <li><a href='?p=cart'>Return to Carts</a></li>
                            <li><a href='?p=cart&a=edit_cart&cartID=<?php echo $cartID; ?>'>Reload this Cart</a></li>
                        </ul>
                    </div>
                    <?php
                    // Failure
                } else {
                    ?>
                    <div class="alert alert-block alert-error">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <h4>Error!</h4>
                        Unable to save Cart. Details have been logged and emailed to the administrator. Click <a href='?p=cart'>here</a> to return to Carts.
                    </div>
                    <?php
                }
            }
        }
    }
    exit; // End AJAX
}

?>

<!--BEGIN#cart_admin_cart_container-->
<div id="cart_admin_cart_container" class="container">

<?php
// No Action
if (!isset($_REQUEST['a'])) {
    ?>
        <div class='page-header'>
            <h1>Cart Management<small>&nbsp;
        <?php
        $cart                  = new killerCart\KillerCart(CART_ID);
        $cartCount['Active']   = $cart->getCartCount('Active');
        $cartCount['Inactive'] = $cart->getCartCount('Inactive');
        $cartCount['All']      = $cartCount['Active'] + $cartCount['Inactive'];
        echo "Total (" . $cartCount['All'] . "), Active (" . $cartCount['Active'] . "), Inactive (" . $cartCount['Inactive'] . ")";
        ?>
                </small></h1>
        </div>
        <!--#page-header-->
                    <?php
                    //
                    // ACL Check
                    //
                    if (!empty($globalACL['write'])) {
                        // New Cart Form
                        include(cartPrivateDir . 'templates/'.$_SESSION['cartTheme'].'/admin/cart/cart_new_form.inc.phtml');
                    }

                    // Existing Cart List
                    include(cartPrivateDir . 'templates/'.$_SESSION['cartTheme'].'/admin/cart/cart_list.inc.phtml');

                    // Action Submitted
                } elseif (isset($_REQUEST['a'])) {

        // Action: edit_cart
        if ($_REQUEST['a'] == "edit_cart") {
            
                        // Initialize Cart Object
                        $cart = new killerCart\KillerCart($cartID);
                        
                        // Load selected cart info
                        if (!empty($_REQUEST['cartID'])) {

                            $cartID                = $_REQUEST['cartID'];
                            #$cart->id              = $cartID;
                            $s = $cart->getCartInfo($cartID);
                            $address                = $cart->getCartAddressInfo($s['addressID']);
                            $pg                     = new killerCart\PaymentGateway($cartID);
                            $paymentGatewayIDActive = $pg->getCartsActivePaymentGatewayID($cartID);
                            $cartsPaymentGateways  = $pg->getCartsPaymentGateways($cartID);
                            echo "<h2>" . $s['cartName'] . "<small>&nbsp;";
                        } else {
                            echo '<h2>New Cart <small>&nbsp;';
                        }

                        // Edit Cart (Main)
                        if (empty($_REQUEST['ia'])) {

                            // Step 1: Show Edit Cart Form & inner action menu
                            if (empty($_REQUEST['s'])) {

                                // Cart Edit Form
                                include(cartPrivateDir . 'templates/'.$_SESSION['cartTheme'].'/admin/cart/cart_edit.inc.phtml');

                                // Cart Payment Gateway Form Modal
                                include(cartPrivateDir . 'templates/'.$_SESSION['cartTheme'].'/admin/cart/cart_payment_gateway_new_form.inc.phtml');

                                // Step 2: Save Cart (Add or Edit)
                            } elseif ($_POST['s'] == 2) {

                                echo 'Save Cart</small></h2>';

                                // Success
                                if ($cart->saveCart()) {

                                    // Images
                                    if (!empty($_FILES)) {
                                        if (file_exists($_FILES['files']['tmp_name'][0]) && is_uploaded_file($_FILES['files']['tmp_name'][0])) {
                                            killerCart\Util::handleFileUpload('files', 'image', $cart->id);
                                        }
                                    }

                                    // Set selected Payment Gateway to Active
                                    $pg = new killerCart\PaymentGateway();
                                    $pg->setCartsActivePaymentGateway($cart->id, $cart->paymentGatewayID);
                                    ?>
                        <div class="alert alert-block alert-success span6 offset3">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <h4>Success!</h4>
                            Cart saved successfully. Click <a href='?p=cart'>here</a> to return to Carts.
                        </div>
                    <?php
                    // Failure
                } else {
                    ?>
                        <div class="alert alert-block alert-error span6 offset3">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <h4>Error!</h4>
                            Unable to save Cart. Details have been logged and emailed to the administrator. Click <a href='?p=cart'>here</a> to return to Carts.
                        </div>
                    <?php
                }
            } #end:step
            // Edit Cart (Security)
        } elseif ($_REQUEST['ia'] == 'security') {

            // Step 2: Show Edit Security Form
            if ($_REQUEST['s'] == 2) {
                include(cartPrivateDir . 'templates/'.$_SESSION['cartTheme'].'/admin/cart/cart_security.inc.phtml');
            }

            // Edit Cart (Security Keys)
        } elseif ($_REQUEST['ia'] == 'security_keys') {

            // Step 2: Show Edit Keys Form
            if ($_REQUEST['s'] == 2) {

                include(cartPrivateDir . 'templates/'.$_SESSION['cartTheme'].'/admin/cart/cart_security_keys.inc.phtml');

                // Step 3: Change Encryption Key Passphrase
            } elseif ($_POST['s'] == 3) {

                echo 'Cart Security :: Encryption Keys :: Save</small></h2>';

                // Sanitize (POST only!) passphrase
                $args = array('key_passphrase'         => FILTER_UNSAFE_RAW,
                    'key_passphrase_confirm' => FILTER_UNSAFE_RAW);
                $s    = new killerCart\Sanitize();
                $san  = $s->filterArray(INPUT_POST, $args);
                if (!$san || ($san['key_passphrase'] !== $san['key_passphrase_confirm'])) {
                    $validated = false;
                } else {
                    $validated = true;
                }
                $auth = new killerCart\Auth();

                // Success
                if ($validated === true && $auth->changeCartUserPrivateKeyPassphrase($san['key_passphrase'])) {
                    ?>
                        <div class="alert alert-block alert-success span6 offset3">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <h4>Success!</h4>
                            Passphrase saved successfully. Click <a href='?p=cart'>here</a> to return to Carts.
                        </div>

                    <?php
                    // Failure
                } else {
                    ?>
                        <div class="alert alert-block alert-error span6 offset3">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <h4>Error!</h4>
                            Unable to save Passphrase. Details have been logged and emailed to the administrator. Click <a href='?p=cart'>here</a> to return to Carts.
                        </div>
                    <?php
                }
            }

            // Edit Cart (Payment Gateways)
        } elseif ($_REQUEST['ia'] == 'paymentGatewayNew') {

            // Step 2: Save Payment Gateways Form
            if ($_POST['s'] == 2) {

                echo 'Cart Payment Gateways :: Save</small></h2>';

                // Sanitize (POST only!) passphrase
                $args = array('gatewayName'       => FILTER_UNSAFE_RAW,
                    'gatewayURL'        => FILTER_SANITIZE_URL,
                    'gatewayUsername'   => FILTER_UNSAFE_RAW,
                    'gatewayPassphrase' => FILTER_UNSAFE_RAW,
                    'gatewayTemplate'   => FILTER_UNSAFE_RAW,
                    'gatewayNotes'      => FILTER_UNSAFE_RAW
                );
                $s    = new killerCart\Sanitize();
                $san  = $s->filterArray(INPUT_POST, $args);
                if (!$san) {
                    $validated = false;
                } else {
                    $validated = true;
                }

                // Offline processor?
                if (empty($san['gatewayURL'])) {
                    $isOffline = true;
                } else {
                    $isOffline = false;
                }
                $auth = new killerCart\Auth();

                // Success
                if ($validated === true && $pg->savePaymentGateway($cartID, $san['gatewayName'], $san['gatewayURL'],
                                                                   $san['gatewayUsername'], $san['gatewayPassphrase'],
                                                                   $san['gatewayNotes'], $san['gatewayTemplate'], $isOffline, null)) {
                    ?>
                        <div class="alert alert-block alert-success span6 offset3">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <h4>Success!</h4>
                            Payment Gateway saved successfully. Click <a href='?p=cart'>here</a> to return to Carts.
                        </div>

                    <?php
                    // Failure
                } else {
                    ?>
                        <div class="alert alert-block alert-error span6 offset3">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <h4>Error!</h4>
                            Unable to save Payment Gateway. Details have been logged and emailed to the administrator. Click <a href='?p=cart'>here</a> to return to Carts.
                        </div>
                        <?php
                    }
                }
                // Edit Cart (Taxes)
            } elseif ($_REQUEST['ia'] == 'cartTaxes') {

                // Step 2: Save Payment Gateways Form
                if ($_POST['s'] == 2) {

                    echo 'Cart Taxes :: Save</small></h2>';

                    // State List
                    $stateList = array();
                    foreach (killerCart\Util::getStateList() as $stateCode => $stateLabel) {
                        $stateList[$stateCode] = $stateLabel;
                    }

                    // Sanitize (POST only!)
                    $args = array();
                    foreach ($stateList as $stateCode => $stateLabel) {
                        $args[$stateCode] = array('filter' => FILTER_VALIDATE_FLOAT, 'flags'  => FILTER_FLAG_ALLOW_FRACTION);
                    }
                    $s   = new killerCart\Sanitize();
                    $san = $s->filterArray(INPUT_POST, $args);
                    if (!$san) {
                        $validated = false;
                    } else {
                        $validated = true;
                    }

                    // Validated, save each entry
                    if ($validated === true) {
                        $cart = new killerCart\KillerCart($cartID);
                        foreach ($san as $k => $v) {
                            if (!$cart->setCartTaxRate($cartID, $k, $stateList[$k], $v)) {
                                $validated = false;
                                break;
                            }
                        }
                        ?>
                        <div class="alert alert-block alert-success span6 offset3">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <h4>Success!</h4>
                            Cart Taxes saved successfully. Click <a href='?p=cart'>here</a> to return to Carts.
                        </div>

                    <?php
                    // Failure
                }
                if ($validated === false) {
                    ?>
                        <div class="alert alert-block alert-error span6 offset3">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <h4>Error!</h4>
                            Unable to save Cart Taxes. Details have been logged and emailed to the administrator. Click <a href='?p=cart'>here</a> to return to Carts.
                        </div>
                        <?php
                    }
                }
            } elseif ($_REQUEST['ia'] == 'homepage') {

                // Step 2: Show Edit Cart Homepage Form
                if ($_REQUEST['s'] == 2) {
                    include(cartPrivateDir . 'templates/'.$_SESSION['cartTheme'].'/admin/cart/cart_homepage.inc.phtml');
                }
            

        // Edit Cart (Security Keys)
        } elseif ($_REQUEST['ia'] == 'security_keys') {
        
        // Edit Cart (Terms of Service)
        } elseif ($_REQUEST['ia'] == 'termsOfService') {

            echo 'Cart :: Terms of Service :: Save</small></h2>';

            // Sanitize (POST only!) passphrase
            $args = array('cartID'          => FILTER_SANITIZE_NUMBER_INT);
            $s    = new killerCart\Sanitize();
            $san  = $s->filterArray(INPUT_POST, $args);
            if (!$san) {
                $validated = false;
            } else {
                $validated = true;
            }
            
            // Success
            if ($validated === true && $cart->saveCartTOS($san['cartID'], $_POST['cartToS']))
                ?>
                    <div class="alert alert-block alert-success span6 offset3">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <h4>Success!</h4>
                        Terms of Service saved successfully. Click <a href='?p=cart&a=edit_cart&cartID=<?php echo $san['cartID']; ?>'>here</a> to reload.
                    </div>

                <?php
                // Failure
            } else {
                ?>
                    <div class="alert alert-block alert-error span6 offset3">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <h4>Error!</h4>
                        Unable to save Terms of Service. Details have been logged and emailed to the administrator. Click <a href='?p=cart'>here</a> to return to Carts.
                    </div>
                <?php
            }
            // End POST['ia']
        } #end:edit_cart
    } #end:ifPost
    ?>
</div>
<!--END#cart_admin_cart_container-->