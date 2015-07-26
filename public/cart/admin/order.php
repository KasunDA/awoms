<?php
// Security check: Deny direct file access; must be loaded through index getfile
if (count(get_included_files()) == 1) {
    header("Location: index.php");
    die("403");
}
?>
<?php
/*
 * If: Form Submitted
 * -[View Customer Order Single]
 * --Capture Payment (MercuryPay)
 * --Void Order (MercuryPay)
 * --Refund Order (MercuryPay)
 * --Update Order Status Code (Offline)
 * --Update Order Delivery Status Code
 * --Update Order Product Delivery Status Code
 * -[View Customer Order Multiple]
 * -[View All Orders]
 * Else: Show Form
 */
?>

<!--BEGIN#cart_admin_orders_container-->
<div id="cart_admin_orders_container" class="container">
    <div class="page-header">
        <h1>Order Management
            <?php
            // Customer Form Submitted
            if (isset($_REQUEST['a'])) {

                // Action: view_customer_order_single
                if ($_REQUEST['a'] == 'view_customer_order_single') {

                    // Step 2: View Selected Order
                    if ($_REQUEST['s'] >= 2) {

                        // Load selected order details
                        $order      = new killerCart\Order();
                        $orderID    = $_REQUEST['oid'];
                        $order->id  = $orderID;
                        $o          = $order->getOrderDetails();
                        $customerID = $o['customerID'];

                        // Carts active Payment Gateway
                        $pg   = new killerCart\PaymentGateway();
                        $pgID = $pg->getCartsActivePaymentGatewayID($_SESSION['cartID']);
                        $spg  = $pg->getPaymentGatewayByID($pgID);

                        // Check for inside_action / step 3 form update
                        if (!empty($_POST['ia'])) {
                            \Errors::debugLogger(__FILE__ . ': POST[ia]');

                            // Flash message will default to success unless defined later on as error
                            $alertType = 'success';

                            //
                            // Capture Payment
                            //
                            if ($_POST['ia'] == 'capturePayment') {

                                \Errors::debugLogger(__FILE__ . ': capturePayment');

                                // Carts active Payment Gateway
                                $merchantID = $spg['gatewayUsername'];
                                $passphrase = $spg['gatewayPassphrase'];

                                // @todo Allow for different payment gateway processors
                                // MercuryPay
                                $mp              = new killerCart\MercuryPay();
                                $soap            = $mp->getSoapClient($spg['gatewayURL']);
                                $mpHistory       = $mp->getHistory($orderID, $customerID);
                                $paymentMethodID = $mpHistory['paymentMethodID'];
                                $mpToken         = $mp->getToken($customerID, $paymentMethodID);

                                // Prepare PreAuthCapture Request
                                $operatorID  = $_SESSION['user']['userName'];
                                $tranType    = 'Credit';
                                $tranCode    = 'PreAuthCaptureByRecordNo';
                                $invoiceNo   = $order->id;
                                $refNo       = $invoiceNo;
                                $memo        = "Goin' Postal Online Store ".cartVersion;
                                $recordNo    = $mpToken['recordNo'];
                                $freq        = $mpToken['frequency'];
                                $totalAmount = $o['totalOrderPrice'];
                                $authAmount  = $mpHistory['amount'];
                                $authCode    = $mpHistory['authCode'];
                                $acqRefData  = $mpHistory['acqRefData'];
                                $xml         = $mp->preparePreAuthCaptureRequest($merchantID, $operatorID, $tranType, $tranCode,
                                                                                 $invoiceNo, $memo, $recordNo, $freq, $totalAmount,
                                                                                 $authAmount, $authCode, $acqRefData);

                                // Execute PreAuthCapture Request
                                $res = $mp->creditTransaction($soap, $xml, $passphrase);

                                // Handle PreAuthCapture Response
                                $xmlRes             = $mp->convertReponseToXML($res);
                                $action             = 'preAuthCapture';
                                $returnCode         = $xmlRes->CmdResponse->DSIXReturnCode;
                                $returnStatus       = $xmlRes->CmdResponse->CmdStatus;
                                $returnTextResponse = $xmlRes->CmdResponse->TextResponse;
                                $returnMessage      = $mp->getCmdResponseMsg($xmlRes->CmdResponse->CmdStatus,
                                                                             $xmlRes->CmdResponse->TextResponse);
                                $avsResult          = $xmlRes->TranResponse->AVSResult;
                                $cvvResult          = $xmlRes->TranResponse->CVVResult;
                                $authCode           = $xmlRes->TranResponse->AuthCode;
                                $acqRefData         = $xmlRes->TranResponse->AcqRefData;
                                $recordNo           = $xmlRes->TranResponse->RecordNo;
                                $processData        = $xmlRes->TranResponse->ProcessData;

                                // Log PreAuthCapture Response
                                $mp->logHistory($orderID, $customerID, $paymentMethodID, $action, $totalAmount, $returnCode,
                                                $returnStatus, $returnTextResponse, $returnMessage, $avsResult, $cvvResult, $authCode,
                                                $acqRefData, $refNo, $processData);

                                // Complete order if payment approved
                                if ($returnStatus == 'Approved') {
                                    // Update order status to Paid
                                    $order->setOrderStatusCode($customerID, $orderID, 'PD', $_SESSION['user']['userID']);
                                    $label = 'Capture Payment Successful!';
                                } else {
                                    // Mark order as declined instead of paid
                                    $order->setOrderStatusCode($customerID, $orderID, 'DCL', $_SESSION['user']['userID']);
                                    $alertType = 'error';
                                    $label     = 'Capture Payment Declined!';
                                }

                                //
                                // Void Order (Reversal/VoidSale)
                            //
                            } elseif ($_POST['ia'] == 'voidOrder') {
                                \Errors::debugLogger(__FILE__ . ': Void Order (Reversal*/VoidSale)');

                                // Carts active Payment Gateway
                                $merchantID = $spg['gatewayUsername'];
                                $passphrase = $spg['gatewayPassphrase'];

                                // @todo Allow for different payment gateway processors
                                // MercuryPay
                                $mp              = new killerCart\MercuryPay();
                                $soap            = $mp->getSoapClient($spg['gatewayURL']);
                                // Orders Payment History
                                $mpHistory       = $mp->getHistory($orderID, $customerID);
                                // Payment Method Token
                                $paymentMethodID = $mpHistory['paymentMethodID'];
                                $mpToken         = $mp->getToken($customerID, $paymentMethodID);

                                // Prepare Universal Request Data
                                $operatorID  = $_SESSION['user']['userName'];
                                $tranType    = 'Credit';
                                $invoiceNo   = $order->id;
                                $refNo       = $invoiceNo;
                                $memo        = "Goin' Postal Online Store ".cartVersion;
                                $recordNo    = $mpToken['recordNo'];
                                $freq        = $mpToken['frequency'];
                                $totalAmount = $o['totalOrderPrice'];
                                $authAmount  = $mpHistory['amount'];
                                $authCode    = $mpHistory['authCode'];
                                $acqRefData  = $mpHistory['acqRefData'];
                                $processData = $mpHistory['processData'];

                                // First attempt a Reversal *but only 1 attempt allowed*
                                // If reversal fails, attempt Capture & VoidSale
                                $doVoidSale = true;

                                if (!$mp->orderHasAttemptedReversal($orderID, $customerID)) {

                                    //
                                    // Reversal
                                    //
                                    \Errors::debugLogger(__FILE__ . ': Attempt Reversal');

                                    $tranCode = 'VoidSaleByRecordNo';
                                    $xml      = $mp->prepareVoidSaleRequest($merchantID, $operatorID, $tranType, $tranCode, $invoiceNo,
                                                                            $memo, $recordNo, $freq, $totalAmount, $authAmount,
                                                                            $authCode, $acqRefData, $processData);

                                    // Execute Reversal Request
                                    $res = $mp->creditTransaction($soap, $xml, $passphrase);

                                    // Handle Reversal Response
                                    $xmlRes             = $mp->convertReponseToXML($res);
                                    $action             = 'preAuthReversal';
                                    $returnCode         = $xmlRes->CmdResponse->DSIXReturnCode;
                                    $returnStatus       = $xmlRes->CmdResponse->CmdStatus;
                                    $returnTextResponse = $xmlRes->CmdResponse->TextResponse;
                                    $returnMessage      = $mp->getCmdResponseMsg($xmlRes->CmdResponse->CmdStatus,
                                                                                 $xmlRes->CmdResponse->TextResponse);
                                    $authCode           = $xmlRes->TranResponse->AuthCode;
                                    $acqRefData         = $xmlRes->TranResponse->AcqRefData;
                                    $processData        = $xmlRes->TranResponse->ProcessData;

                                    // Log Reversal Response
                                    $mp->logHistory($orderID, $customerID, $paymentMethodID, $action, $totalAmount, $returnCode,
                                                    $returnStatus, $returnTextResponse, $returnMessage, '', '', $authCode, $acqRefData,
                                                    $refNo, $processData);

                                    // Reverse Succeeded
                                    if ($returnStatus == 'Approved' && ($returnTextResponse == 'REVERSED' || $returnTextResponse == 'APPROVED STANDIN')) {
                                        \Errors::debugLogger(__FILE__ . ': Reverse Succeeded');
                                        // Update order status to Void
                                        $order->setOrderStatusCode($customerID, $orderID, 'VD', $_SESSION['user']['userID']);
                                        $order->setOrderDeliveryStatusCode($customerID, $orderID, 'CNC', $_SESSION['user']['userID']);
                                        $alertType  = 'success';
                                        $label      = 'Reversal Successful!';
                                        $doVoidSale = false;
                                    } else {
                                        // Update history for VoidSale
                                        $mpHistory = $mp->getHistory($orderID, $customerID);
                                    }
                                }

                                //
                                // PreAuthCapture & VoidSale
                                //
                                if (!empty($doVoidSale)) {

                                    //
                                    // PreAuthCapture
                                    //
                                    \Errors::debugLogger(__FILE__ . ': PreAuthCapture');

                                    // Prepare PreAuthCapture Request
                                    $tranCode = 'PreAuthCaptureByRecordNo';
                                    $xml      = $mp->preparePreAuthCaptureRequest($merchantID, $operatorID, $tranType, $tranCode,
                                                                                  $invoiceNo, $memo, $recordNo, $freq, $totalAmount,
                                                                                  $authAmount, $authCode, $acqRefData);

                                    // Execute PreAuthCapture Request
                                    $res = $mp->creditTransaction($soap, $xml, $passphrase);

                                    // Handle PreAuthCapture Response
                                    $xmlRes             = $mp->convertReponseToXML($res);
                                    $action             = 'preAuthCapture';
                                    $returnCode         = $xmlRes->CmdResponse->DSIXReturnCode;
                                    $returnStatus       = $xmlRes->CmdResponse->CmdStatus;
                                    $returnTextResponse = $xmlRes->CmdResponse->TextResponse;
                                    $returnMessage      = $mp->getCmdResponseMsg($xmlRes->CmdResponse->CmdStatus,
                                                                                 $xmlRes->CmdResponse->TextResponse);
                                    $avsResult          = $xmlRes->TranResponse->AVSResult;
                                    $cvvResult          = $xmlRes->TranResponse->CVVResult;
                                    $authCode           = $xmlRes->TranResponse->AuthCode;
                                    $acqRefData         = $xmlRes->TranResponse->AcqRefData;
                                    $recordNo           = $xmlRes->TranResponse->RecordNo;
                                    $processData        = $xmlRes->TranResponse->ProcessData;

                                    // Log PreAuthCapture Response
                                    $mp->logHistory($orderID, $customerID, $paymentMethodID, $action, $totalAmount, $returnCode,
                                                    $returnStatus, $returnTextResponse, $returnMessage, $avsResult, $cvvResult,
                                                    $authCode, $acqRefData, $refNo, $processData);

                                    //
                                    // VoidSale
                                    //
                                    \Errors::debugLogger(__FILE__ . ': VoidSale');

                                    // @todo use new history values?
                                    $recordNo = $mpToken['recordNo'];
                                    $authCode = $mpHistory['authCode'];

                                    // Prepare VoidSale Request
                                    $tranCode = 'VoidSaleByRecordNo';
                                    $xml      = $mp->prepareVoidSaleRequest($merchantID, $operatorID, $tranType, $tranCode, $invoiceNo,
                                                                            $memo, $recordNo, $freq, $totalAmount, $authAmount,
                                                                            $authCode, false, false);
                                    // Execute VoidSale Request
                                    $res      = $mp->creditTransaction($soap, $xml, $passphrase);

                                    // Handle VoidSale Response
                                    $xmlRes             = $mp->convertReponseToXML($res);
                                    $action             = 'VoidSale';
                                    $returnCode         = $xmlRes->CmdResponse->DSIXReturnCode;
                                    $returnStatus       = $xmlRes->CmdResponse->CmdStatus;
                                    $returnTextResponse = $xmlRes->CmdResponse->TextResponse;
                                    $returnMessage      = $mp->getCmdResponseMsg($xmlRes->CmdResponse->CmdStatus,
                                                                                 $xmlRes->CmdResponse->TextResponse);
                                    $authCode           = $xmlRes->TranResponse->AuthCode;
                                    $acqRefData         = $xmlRes->TranResponse->AcqRefData;
                                    $processData        = $xmlRes->TranResponse->ProcessData;

                                    // Log VoidSale Response
                                    $mp->logHistory($orderID, $customerID, $paymentMethodID, $action, $totalAmount, $returnCode,
                                                    $returnStatus, $returnTextResponse, $returnMessage, '', '', $authCode, $acqRefData,
                                                    $refNo, $processData);

                                    // Complete order if payment approved
                                    if ($returnStatus == 'Approved') {
                                        // Update order status to Paid
                                        $order->setOrderStatusCode($customerID, $orderID, 'VD', $_SESSION['user']['userID']);
                                        $order->setOrderDeliveryStatusCode($customerID, $orderID, 'CNC', $_SESSION['user']['userID']);
                                        $alertType = 'success';
                                        $label     = 'Void Successful!';
                                    } else {
                                        $alertType = 'error';
                                        $label     = 'Void Failed!';
                                    }
                                }

                                //
                                // Refund Order
                            //
                            } elseif ($_POST['ia'] == 'refundOrder') {


                                \Errors::debugLogger(__FILE__ . ': refundOrder');

                                // Carts active Payment Gateway
                                $merchantID = $spg['gatewayUsername'];
                                $passphrase = $spg['gatewayPassphrase'];

                                // @todo Allow for different payment gateway processors
                                // MercuryPay
                                $mp              = new killerCart\MercuryPay();
                                $soap            = $mp->getSoapClient($spg['gatewayURL']);
                                $mpHistory       = $mp->getHistory($orderID, $customerID);
                                $paymentMethodID = $mpHistory['paymentMethodID'];
                                $mpToken         = $mp->getToken($customerID, $paymentMethodID);

                                // Prepare Refund Request
                                $operatorID  = $_SESSION['user']['userName'];
                                $tranType    = 'Credit';
                                $tranCode    = 'ReturnByRecordNo';
                                $invoiceNo   = $order->id;
                                $refNo       = $invoiceNo;
                                $memo        = "Goin' Postal Online Store ".cartVersion;
                                $recordNo    = $mpToken['recordNo'];
                                $freq        = $mpToken['frequency'];
                                $totalAmount = $o['totalOrderPrice'];
                                $xml         = $mp->prepareReturnRequest($merchantID, $operatorID, $tranType, $tranCode, $invoiceNo,
                                                                         $memo, $recordNo, $freq, $totalAmount);

                                // Execute Refund Request
                                $res = $mp->creditTransaction($soap, $xml, $passphrase);

                                // Handle Refund Response
                                $xmlRes             = $mp->convertReponseToXML($res);
                                $action             = 'return';
                                $returnCode         = $xmlRes->CmdResponse->DSIXReturnCode;
                                $returnStatus       = $xmlRes->CmdResponse->CmdStatus;
                                $returnTextResponse = $xmlRes->CmdResponse->TextResponse;
                                $returnMessage      = $mp->getCmdResponseMsg($xmlRes->CmdResponse->CmdStatus,
                                                                             $xmlRes->CmdResponse->TextResponse);
                                $authCode           = $xmlRes->TranResponse->AuthCode;
                                $acqRefData         = $xmlRes->TranResponse->AcqRefData;
                                $recordNo           = $xmlRes->TranResponse->RecordNo;
                                $processData        = $xmlRes->TranResponse->ProcessData;

                                // Log Refund Response
                                $mp->logHistory($orderID, $customerID, $paymentMethodID, $action, $totalAmount, $returnCode,
                                                $returnStatus, $returnTextResponse, $returnMessage, '', '', $authCode, $acqRefData,
                                                $refNo, $processData);

                                // Update order status if refund approved
                                if ($returnStatus == 'Approved') {
                                    // Update order status to Refunded
                                    $order->setOrderStatusCode($customerID, $orderID, 'RFN', $_SESSION['user']['userID']);
                                    $label = 'Refund was Successful!';
                                } else {
                                    $alertType = 'error';
                                    $label     = 'Refund was Declined!';
                                }

                                //
                                // Update Order Status Code
                            //
                            } elseif ($_POST['ia'] == 'updateOrderStatusCode') {
                                $order->setOrderStatusCode($customerID, $orderID, $_POST['orderStatusCode'], $_SESSION['user']['userID']);
                                $label = 'Order Status Updated!';

                                //
                                // Update Order Delivery Status Code
                            //
                            } elseif ($_POST['ia'] == 'update_orderDeliveryStatusCode') {
                                $order->setOrderDeliveryStatusCode($customerID, $orderID, $_POST['orderDeliveryStatusCode'],
                                                                   $_SESSION['user']['userID']);
                                $label = 'Order Delivery Status Updated!';

                                // Update order total when shipping completed
                                if (in_array($_POST['orderDeliveryStatusCode'], array('CMP', 'SHP'))) {
                                    $order_product_subtotal          = 0;
                                    $order_product_delivery_subtotal = 0;
                                    // FUTURE: $order_product_tax_subtotal = 0;	
                                    $p                               = new killerCart\Product();
                                    foreach ($order->getOrderProducts($orderID) as $op) {
                                        $p                       = $p->getProductInfo($op['productID']);
                                        $p['deliveryStatusCode'] = $order->getOrderProductCurrentDeliveryInfo($orderID, $p['productID']);
                                        $order_product_subtotal += $p['price'] * $op['quantity'];
                                        $order_product_delivery_subtotal += $p['deliveryStatusCode']['deliveryPrice'];
                                        $order_grand_total       = $order_product_subtotal + $order_product_delivery_subtotal;
                                    }
                                    $order->setOrderTotalPrice($orderID, $order_grand_total);
                                }

                                //
                                // Update Order Product Delivery Status Code
                            //
                            } elseif ($_POST['ia'] == 'update_order_product_delivery') {
                                $order->updateOrderProductDelivery();
                                // If all products in order are shipped, automagically set order delivery status to shipped as well
                                // as add total price with all shipping costs
                                if ($order->getOrderProductsDeliveryReady($orderID)) {
                                    $order->setOrderDeliveryStatusCode($customerID, $orderID, 'SHP', $_SESSION['user']['userID']);
                                    $label         = 'Product & Order Delivery Updated!';
                                    // Update order total when shipping completed
                                    $totalDelivery = 0;
                                    foreach ($order->getOrderProducts($orderID) as $op) {
                                        $productDeliveryInfo = $order->getOrderProductCurrentDeliveryInfo($orderID, $op['productID']);
                                        $totalDelivery += $productDeliveryInfo['deliveryPrice'];
                                    }
                                    $order->setOrderTotalDelivery($orderID, $totalDelivery);
                                    $orderTotal = $order->getOrderTotalAmount($orderID);
                                    $orderTotal += $totalDelivery;
                                    $order->setOrderTotalPrice($orderID, $orderTotal);
                                } else {
                                    $label = 'Product Delivery Updated!';
                                }
                            }
                        } // End s>2
                        //
                        // Order Details
                        // 
                        // If any product in order does not have shipping completed, disable master shipping select field
                        $enableOrderDeliveryStatus = '';
                        foreach ($order->getOrderProducts($orderID) as $op) {
                            $p                     = new killerCart\Product();
                            $p->id                 = $op['productID'];
                            $p->deliveryStatusCode = $order->getOrderProductCurrentDeliveryInfo($orderID, $p->id);
                            if (!in_array($p->deliveryStatusCode['deliveryStatusCode'], array('CNC', 'SHP', 'CMP'))) {
                                $enableOrderDeliveryStatus = ' disabled';
                                break;
                            }
                        }

                        // Get updated order details
                        $o                          = $order->getOrderDetails();
                        $orderCurStatusCode         = $order->getOrderCurrentStatusCode();
                        $orderCurDeliveryStatusCode = $order->getOrderCurrentDeliveryStatusCode();
                        if (empty($orderCurStatusCode) || $orderCurStatusCode['orderStatusCode'] == 'INC') {
                            $pmType                      = '';
                            $pmDesc                      = '';
                            $pmNotes                     = '';
                            $number1masked               = '';
                            $number1plain                = '';
                            $number2                     = '';
                            $number3                     = '';
                            $expMonth                    = '';
                            $expYear                     = '';
                            $avsResult                   = '';
                            $cvvResult                   = '';
                            $deliveryClass               = 'error';
                            $orderClass                  = 'error';
                            $enableOrderDeliveryStatus   = ' disabled';
                            $enableProductDeliveryStatus = ' disabled';
                            $enableOrderStatus           = ' disabled';
                        } else {

                            // ACL: Not allowed to change shipping
                            if (empty($_SESSION['user']['ACL']['shipping']['write'])) {
                                $enableOrderDeliveryStatus   = ' disabled';
                                $enableProductDeliveryStatus = ' disabled';
                            } else {
                                $enableProductDeliveryStatus = '';
                            }

                            // ACL: Allowed to see billing (and order has billing info)
                            if (!empty($_SESSION['user']['ACL']['billing']['read']) && $o['totalOrderPrice'] != '0.00') {

                                // Payment Method Details
                                $pm   = $order->getOrderPaymentMethod();
                                $auth = new killerCart\Auth();
                                // Decrypt
                                if (in_array($orderCurStatusCode['orderStatusCode'], array('INC', 'CNC', 'DCL'))) {
                                    $number3  = $auth->decryptData($pm['number3'], $_SESSION['unprotPrivKey']);
                                    $expMonth = $auth->decryptData($pm['expMonth'], $_SESSION['unprotPrivKey']);
                                    $expYear  = $auth->decryptData($pm['expYear'], $_SESSION['unprotPrivKey']);
                                } else {
                                    $number3  = $pm['number3'];
                                    $expMonth = $pm['expMonth'];
                                    $expYear  = $pm['expYear'];
                                }

                                // Payment Method Info
                                $pmType  = $pm['paymentMethodCode'];
                                $pmDesc  = $pm['paymentMethodDescription'];
                                $pmNotes = $pm['paymentMethodNotes'];

                                // If already masked dont mask again and cvv is blank / hide 'number2 & 3' rows
                                $number1plain = $auth->decryptData($pm['number1'], $_SESSION['unprotPrivKey']);
                                if (strstr($number1plain, '*')) {
                                    $number1masked = substr($number1plain, 0, 6) . '******' . $number3;
                                    $number1plain  = $number1masked;
                                    $number2       = '';
                                    $number3       = '';
                                } else {
                                    $number1masked = '************' . $number3;
                                    $number2       = $auth->decryptData($pm['number2'], $_SESSION['unprotPrivKey']);
                                    $number3       = '';
                                }

                                // @todo Allow for different payment gateway processors
                                // MercuryPay
                                $mp        = new killerCart\MercuryPay();
                                $mpHistory = $mp->getHistory($orderID, $customerID);
                                $avsResult = $mpHistory['avsResult'];
                                $cvvResult = $mpHistory['cvvResult'];

                                // ACL: Not allowed to see billing
                            } else {
                                if ($o['totalOrderPrice'] == '0.00') {
                                    $pmDesc        = 'N/A';
                                    $number1masked = 'N/A';
                                    $number1plain  = 'N/A';
                                } else {
                                    $pmDesc        = 'Restricted';
                                    $number1masked = 'Restricted';
                                    $number1plain  = 'Restricted';
                                }
                                $pmType    = '';
                                $pmNotes   = '';
                                $number2   = '';
                                $number3   = '';
                                $expMonth  = '';
                                $expYear   = '';
                                $avsResult = '';
                                $cvvResult = '';
                            }

                            // Disable Update Order Status Button unless in Offline processing & in state that can be changed
                            // [x] ACL
                            $enableOrderStatus = ' disabled';
                            if (!empty($_SESSION['user']['ACL']['billing']['write'])) {
                                if (!empty($spg['gatewayOffline']) || $o['totalOrderPrice'] == '0.00') {
                                    if (in_array($orderCurStatusCode['orderStatusCode'], array('PND', 'ATH', 'PD', 'CMP'))) {
                                        $enableOrderStatus = '';
                                    }
                                }
                            }

                            // Delivery Status
                            // Success/EnableOrder = Shipped, Completed, Archived
                            if (in_array($orderCurDeliveryStatusCode['deliveryStatusCode'], array('SHP', 'CMP', 'ARC'))) {
                                $deliveryClass = 'success';

                                // Info/DisableOrder = Ready, Partially Shipped, Partially Returned
                            } elseif (in_array($orderCurDeliveryStatusCode['deliveryStatusCode'], array('RDY', 'SPP', 'RTP'))) {
                                $deliveryClass = 'info';

                                // Warning/DisableOrder = Pending
                            } elseif (in_array($orderCurDeliveryStatusCode['deliveryStatusCode'], array('PND'))) {
                                $deliveryClass = 'warning';

                                // Error/EnableOrder = Cancelled, Returned
                            } elseif (in_array($orderCurDeliveryStatusCode['deliveryStatusCode'], array('CNC', 'RTN'))) {
                                $deliveryClass = 'error';
                            }

                            // Success = Paid/Complete/Archived
                            if (in_array($orderCurStatusCode['orderStatusCode'], array('PD', 'CMP', 'ARC'))) {
                                $orderClass = 'success';

                                // Authorized
                            } elseif (in_array($orderCurStatusCode['orderStatusCode'], array('ATH'))) {
                                $orderClass = 'info';

                                // Cancelled/Declined/Refunded/Voided
                            } elseif (in_array($orderCurStatusCode['orderStatusCode'], array('CNC', 'DCL', 'RFN', 'VD'))) {
                                $orderClass = 'error';

                                // Pending
                            } else {
                                $orderClass = 'warning';
                            }
                        }

                        // Edit Selected Orders
                        include(cartPrivateDir . 'templates/'.$_SESSION['cartTheme'].'/admin/order/order_edit.inc.phtml');
                    } #end:Step 2
                    //Action: view_customer_order_multiple
                } elseif ($_REQUEST['a'] == "view_customer_order_multiple") {

                    // Action: view_all_orders
                } elseif ($_REQUEST['a'] == "view_all_orders") {

                    // Step: 2 View All Orders
                    if ($_REQUEST['s'] == 2) {

                        // Existing Orders List
                        include(cartPrivateDir . 'templates/'.$_SESSION['cartTheme'].'/admin/order/order_list.inc.phtml');
                    } #end:Step2
                } #endif:a=get_orders
                // endif:Post[a]
                // No Form Submitted - Select Cart/Customer to view
            } else {

                // Existing Orders List
                include(cartPrivateDir . 'templates/'.$_SESSION['cartTheme'].'/admin/order/order_list.inc.phtml');
            }
            ?>

            <!--
            <small>Select a cart or customer to view orders</small></h1></div>
    
            <form id='frm_choose_customer' class='form-horizontal' method='post'>
                <input type="hidden" name="s" value="2" />
                <input type="hidden" name="p" value="<?php echo $_REQUEST['p']; ?>" />
    
                <div class="control-group">
                    <label class="control-label" for="cartID">Select Cart</label>
                    <div class="controls">
                        <div class='span3'>
                            <select name='cartID' id='cartID' disabled>
                                <option disabled selected>--Select Cart--</option>
                                <option value='1'>(1) Alpha Cart</option>
                                <option value='2' disabled>(2) Beta Cart</option>
                            </select>
                        </div>
                        <div class='span3'>
                            <button type="submit" id="btn_this_carts_orders_submit" name="a" value="view_carts_orders" class="btn tooltip-on" data-toggle="tooltip" title='View all orders of the selected cart' disabled><i class='icon-ok-sign'></i>&nbsp;View This Carts Orders</button>
                        </div>
                    </div>
                </div>
    
                <div class="control-group">
                    <label class="control-label" for="customerID">Select Customer</label>
                    <div class="controls">
                        <div class='span3'>
                            <div id='customerID_dynamic'><span class='muted'>Select cart first</span></div>
                        </div>
                        <div class='span4'>
                            <button type="submit" id="btn_this_customers_orders_submit" name="a" value="view_customer_order_multiple" class="btn tooltip-on" data-toggle="tooltip" title='View all orders of the selected customer' disabled><i class='icon-ok-sign'></i>&nbsp;View This Customers Orders</button>
                        </div>
                    </div>
                </div>
    
                <div class="control-group">
                    <div class="controls">
                        <div class='span3'>
                        </div>
                        <div class='span3'>
                            <button type="submit" id="btn_all_orders_submit" name="a" value="view_all_orders" class="btn tooltip-on" data-toggle="tooltip" title='View all orders of all carts'><i class='icon-ok-sign'></i>&nbsp;View All Orders</button>
                        </div>
                    </div>
                </div>
    
            </form>
            -->
    </div>
    <!--END#cart_admin_orders_container-->