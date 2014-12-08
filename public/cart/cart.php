<?php

// Security check: Deny direct file access; must be loaded through index getfile
if (count(get_included_files()) == 1) {
    header("Location: index.php");
    die("403");
}
?>

<?php
//
// Cart Form Submitted
//
if (isset($_POST['a'])
        || isset($_REQUEST['empty'])) {

    //
    // Update Product in Cart
    //
    if (isset($_POST['a'])
            && $_POST['a'] == "update_product_in_cart") {

        // Validate form (POST only!)
        if (empty($_POST['productID'])) {
            $validated     = false;
            $validationMsg = 'Empty Product ID';
        } else {
            // Sanitize (POST only!)
            $args = array('productID'  => FILTER_VALIDATE_INT,
                'productQty' => FILTER_VALIDATE_INT
            );
            $s    = new killerCart\Sanitize();
            $san  = $s->filterArray(INPUT_POST, $args);
            if (!$san) {
                $validated     = false;
                $validationMsg = 'Product Post Failed SAN';
            } else {
                $validated = true;
            }
        }
        if (empty($validated)) {
            trigger_error(__FILE__ . '@' . __LINE__ . " Unexpected results: " . $validationMsg, E_USER_ERROR);
            return false;
        }
        // Add custom choices
        foreach ($_POST as $k => $v) {
            // Skips previously added & empty values
            if (isset($san[$k]) || $v == '') {
                continue;
            }
            // Text/Textarea options have Choice ID and need sanitizing
            if (preg_match('/^(option)(\d+)(Choice)(\d+)/', $k, $matches)) {
                $san[$matches[0]] = killerCart\Util::convertNLToBR(htmlspecialchars($v, ENT_NOQUOTES));
                // Select/Checkbox/Radio options have no Choice ID
            } elseif (preg_match('/^(option)(\d+)(Choice)/', $k, $matches)) {
                $san[$k] = $v;
            }
        }

        // Set Product ID from form
        $product = new killerCart\Product();
        $pInfo   = $product->getProductInfo($san['productID']);

        // Set Quantity from form (assumes 1 if not set, 0 if remove item clicked)
        if (!isset($san['productQty'])) {
            $quantity = 1;
        } else {
            $quantity = $san['productQty'];
        }

        //
        // Update Product in Cart
        //
        $cart->updateProductQtyInCart($san['productID'], $pInfo['productName'], $quantity, $pInfo['price']);

        //
        // Update Product Options in Cart
        //
        foreach ($san as $k => $v) {
            if (preg_match('/^(option)(\d+)(Choice)/', $k, $matches)) {
                $optionID = $matches[2];
                // Array of selected choices = checkbox
                if (is_array($v)) {
                    foreach ($v as $choiceID) {
                        $choiceInfo  = $product->getProductOptionsChoicesCustom($optionID, $choiceID);
                        $choicePrice = killerCart\Util::getFormattedNumber($choiceInfo['choicePriceCustom']);
                        $choiceValue = $choiceInfo['choiceValueCustom'];
                        if (!empty($choiceInfo['choiceImageIDCustom'])) {
                            $choiceImageID = $choiceInfo['choiceImageIDCustom'];
                        } else {
                            $choiceImageID = NULL;
                        }
                        $cart->updateProductOptionInCart($san['productID'], $optionID, $choiceID, $choicePrice, $choiceValue,
                                                         $choiceImageID);
                    }
                    // Everything else
                } else {
                    $optionInfo    = $product->getProductOptionsCustom($cart->session['cartID'], $san['productID'], $optionID);
                    $optionType    = $optionInfo['optionType'];
                    $choiceImageID = NULL;
                    if (in_array($optionType, array('textarea', 'text'))) {
                        if (preg_match('/^(option)(\d+)(Choice)(\d+)/', $k, $matches)) {
                            $choiceID    = $matches[4];
                            $choiceInfo  = $product->getProductOptionsChoicesCustom($optionID, $choiceID);
                            $choiceValue = $v;
                        }
                    } else {
                        $choiceID    = $v;
                        $choiceInfo  = $product->getProductOptionsChoicesCustom($optionID, $choiceID);
                        $choiceValue = $choiceInfo['choiceValueCustom'];
                        if (!empty($choiceInfo['choiceImageIDCustom'])) {
                            $choiceImageID = $choiceInfo['choiceImageIDCustom'];
                        }
                    }
                    $choicePrice = killerCart\Util::getFormattedNumber($choiceInfo['choicePriceCustom']);
                    $cart->updateProductOptionInCart($san['productID'], $optionID, $choiceID, $choicePrice, $choiceValue,
                                                     $choiceImageID);
                }
            }
        }

        // Save Cart Session to DB
        $cart->saveSession();
    } #endif:update_product_in_cart
    // Action: Empty Cart
    elseif (isset($_REQUEST['empty']) || $_POST['a'] == "empty_cart") {
        include(cartPrivateDir . 'templates/' . $cart->session['cartTheme'] . '/cart/cart_empty.inc.phtml');
    } #endif:emptyCart
} #endif:_POST
// Show Cart CartFront
include(cartPrivateDir . 'templates/' . $cart->session['cartTheme'] . '/cart/cart_view.inc.phtml');
?>
