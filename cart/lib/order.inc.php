<?php
namespace killerCart;

/**
 * Order class
 * 
 * Order management methods
 *
 * PHP version 5
 *
 * @category  killerCart
 * @package   killerCart
 * @author    Brock Hensley <brock@brockhensley.com>
 * @version   v0.0.1
 */
class Order extends Cart
{
    /**
     * __construct
     */
    public function __construct()
    {
        \Errors::debugLogger(__METHOD__, 5);
        $this->DB = new \Database();
    }

    /**
     * getActiveOrders
     * 
     * Gets all active orders that are marked as pending/authorized
     * 
     * @param int $cartID Cart ID to filter
     * 
     * @return array Order data
     */
    public function getActiveOrders($cartID)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql = "
			SELECT
                co.orderID, co.customerID, co.orderStatusCode, co.deliveryStatusCode, co.paymentMethodID, co.dateOrderPlaced,
                co.dateOrderPaid, co.totalOrderPrice, co.orderNotes,
                ocode.orderStatusDescription,
                dcode.deliveryStatusDescription,
                c.cartID, c.customCustomerID
			FROM customerOrders AS co
                INNER JOIN refOrderStatusCodes AS ocode
                    ON co.orderStatusCode = ocode.orderStatusCode
                INNER JOIN refDeliveryStatusCodes AS dcode
                    ON co.deliveryStatusCode = dcode.deliveryStatusCode
                INNER JOIN customers AS c
                    ON co.customerID = c.customerID
            WHERE co.orderStatusCode IN ('PND', 'ATH')";
        if (!empty($cartID)) {
            $this->sql .= " AND c.cartID = " . $cartID;
        }
        $this->sql .= " ORDER BY co.orderID DESC";
        return $this->DB->query($this->sql);
    }

    /**
     * getSettledOrders
     * 
     * Gets all orders that are marked as paid/refunded/voided/completed/archived
     * 
     * @param int $cartID Cart ID to filter
     * 
     * @return array Order data
     */
    public function getSettledOrders($cartID)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql = "
			SELECT
                co.orderID, co.customerID, co.orderStatusCode, co.deliveryStatusCode, co.paymentMethodID, co.dateOrderPlaced,
                co.dateOrderPaid, co.totalOrderPrice, co.orderNotes,
                ocode.orderStatusDescription,
                dcode.deliveryStatusDescription,
                c.cartID, c.customCustomerID
			FROM customerOrders AS co
                INNER JOIN refOrderStatusCodes AS ocode
                    ON co.orderStatusCode = ocode.orderStatusCode
                INNER JOIN refDeliveryStatusCodes AS dcode
                    ON co.deliveryStatusCode = dcode.deliveryStatusCode
                INNER JOIN customers AS c
                    ON co.customerID = c.customerID
            WHERE co.orderStatusCode IN ('PD', 'RFN', 'VD', 'CMP', 'ARC')";
        if (!empty($cartID)) {
            $this->sql .= " AND c.cartID = " . $cartID;
        }
        $this->sql .= " ORDER BY co.orderID DESC";
        return $this->DB->query($this->sql);
    }

    /**
     * getUnsettledOrders
     * 
     * Gets all orders that are marked as declined/cancelled
     * 
     * @param int $cartID Cart ID to filter
     * 
     * @return array
     */
    public function getUnsettledOrders($cartID)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql = "
			SELECT
                co.orderID, co.customerID, co.orderStatusCode, co.deliveryStatusCode, co.paymentMethodID, co.dateOrderPlaced,
                co.dateOrderPaid, co.totalOrderPrice, co.orderNotes,
                ocode.orderStatusDescription,
                dcode.deliveryStatusDescription,
                c.cartID, c.customCustomerID
			FROM customerOrders AS co
                INNER JOIN refOrderStatusCodes AS ocode
                    ON co.orderStatusCode = ocode.orderStatusCode
                INNER JOIN refDeliveryStatusCodes AS dcode
                    ON co.deliveryStatusCode = dcode.deliveryStatusCode
                INNER JOIN customers AS c
                    ON co.customerID = c.customerID
            WHERE co.orderStatusCode IN ('DCL', 'CNC')";
        if (!empty($cartID)) {
            $this->sql .= " AND c.cartID = " . $cartID;
        }
        $this->sql .= " ORDER BY co.orderID DESC";
        return $this->DB->query($this->sql);
    }

    /**
     * getIncompleteOrders
     * 
     * Gets all orders that are marked as incomplete
     * 
     * @param int $cartID Cart ID to filter
     * 
     * @return array
     */
    public function getIncompleteOrders($cartID)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql = "
			SELECT
                co.orderID, co.customerID, co.orderStatusCode, co.deliveryStatusCode, co.paymentMethodID, co.dateOrderPlaced,
                co.dateOrderPaid, co.totalOrderPrice, co.orderNotes,
                ocode.orderStatusDescription,
                dcode.deliveryStatusDescription,
                c.cartID, c.customCustomerID
			FROM customerOrders AS co
                INNER JOIN refOrderStatusCodes AS ocode
                    ON co.orderStatusCode = ocode.orderStatusCode
                INNER JOIN refDeliveryStatusCodes AS dcode
                    ON co.deliveryStatusCode = dcode.deliveryStatusCode
                INNER JOIN customers AS c
                    ON co.customerID = c.customerID
            WHERE co.orderStatusCode IN ('INC')";
        if (!empty($cartID)) {
            $this->sql .= " AND c.cartID = " . $cartID;
        }
        $this->sql .= " ORDER BY co.orderID DESC";
        return $this->DB->query($this->sql);
    }

    /**
     * getOrderCount
     * 
     * Gets count of orders matching selected status code
     * 
     * @param string|array $type Type of order (string for single or array for multiple csv)
     * @param int $cartID Cart ID to filter
     * 
     * @return int Count of orders
     */
    public function getOrderCount($type, $cartID)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        if (is_array($type)) {
            $type     = implode("','", $type);
            $whereSql = "orderStatusCode IN ('" . $type . "')";
        } elseif (strtolower($type) == "all") {
            $whereSql = 'orderID IS NOT NULL';
        } else {
            $whereSql = "orderStatusCode = '" . $type . "'";
        }
        if (!empty($cartID)) {
            $whereSql .= ' AND c.cartID = :cartID';
        }
        $this->sql     = "
			SELECT COUNT(co.orderID) AS Total
			FROM customerOrders AS co
                INNER JOIN customers AS c
                    ON co.customerID = c.customerID
			WHERE " . $whereSql;
        $this->sqlData = array(':cartID' => $cartID);
        $return_arr    = $this->DB->query($this->sql, $this->sqlData);
        if (!empty($return_arr)) {
            return $return_arr[0]['Total'];
        } else {
            \Errors::debugLogger(__METHOD__, 5);
            return false;
        }
    }

    /**
     * getOrderDetails
     * 
     * Gets details of order
     * 
     * @version v0.0.1
     * 
     * @param int Order ID ($this->id)
     * 
     * @return array Order details
     */
    public function getOrderDetails()
    {
        \Errors::debugLogger(__METHOD__, 5);
        $this->sql = "
			SELECT customerID, paymentMethodID, dateOrderPlaced, dateOrderPaid, totalOrderPrice, orderNotes,
                    shippingAddressID
			FROM customerOrders
			WHERE orderID = :orderID";
        $sql_data  = array(':orderID' => $this->id);
        $res       = $this->DB->query($this->sql, $sql_data);
        if (!isset($res)) {
            trigger_error('Unexpected results.', E_USER_ERROR);
            return false;
        }
        return $res[0];
    }

    /**
     * getOrderProducts
     * 
     * Gets products in order
     * 
     * @version v0.0.1
     * 
     * @param int $orderID Order ID
     * 
     * @return array Order products
     */
    public function getOrderProducts($orderID)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql     = "
			SELECT cop.productID, cop.quantity, cop.comments,
                p.productEmail, p.productName, p.productSKU
			FROM customerOrderProducts AS cop
                INNER JOIN products AS p
                    ON p.productID = cop.productID
			WHERE cop.orderID = :orderID";
        $this->sqlData = array(':orderID' => $orderID);
        $res           = $this->DB->query($this->sql, $this->sqlData);
        if (!isset($res)) {
            trigger_error('Unexpected results.', E_USER_ERROR);
            return false;
        }
        return $res;
    }

    /**
     * getOrderProductsOptions
     * 
     * Gets product options in order if any were selected
     * 
     * @version v0.0.1
     * 
     * @since v0.0.1
     * 
     * @param int $orderID Order ID
     * @param int $productID Product ID
     * @param int $optionID Optional Option ID to narrow results
     * 
     * @return array Order product options
     * 
     * @todo optionPrice
     */
    public function getOrderProductsOptions($orderID, $productID, $optionID = NULL)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        //, optionPrice
        $this->sql     = "
			SELECT copoID, orderID, productID, productOptionCustomID, productOptionChoiceCustomID, optionValue
			FROM customerOrderProductsOptions
			WHERE orderID = :orderID
            AND productID = :productID";
        $this->sqlData = array(':orderID'   => $orderID, ':productID' => $productID);
        if (!empty($optionID)) {
            $this->sql .= " AND productOptionCustomID = :productOptionCustomID";
            $this->sqlData[':productOptionCustomID'] = $optionID;
        }
        return $this->DB->query($this->sql, $this->sqlData);
    }

    /**
     * getOrderPaymentMethod
     * 
     * Gets payment method used in order
     * 
     * @return array Order payment method
     */
    public function getOrderPaymentMethod()
    {
        \Errors::debugLogger(__METHOD__, 5);
        $this->sql     = "
			SELECT cpm.paymentMethodCode,
					ref.paymentMethodDescription,
					pm.*
			FROM customerOrders AS co
				INNER JOIN customerPaymentMethods AS cpm
					ON co.paymentMethodID = cpm.paymentMethodID
				INNER JOIN paymentMethods AS pm
					ON cpm.paymentMethodID = pm.paymentMethodID
				INNER JOIN refPaymentMethodTypes AS ref
					ON cpm.paymentMethodCode = ref.paymentMethodCode
			WHERE co.orderID = :orderID";
        $this->sqlData = array(':orderID' => $this->id);
        $res           = $this->DB->query($this->sql, $this->sqlData);
        if (!isset($res) || empty($res)) {
            //trigger_error('Unexpected results.', E_USER_ERROR);
            return false;
        }
        return $res[0];
    }

    /**
     * getOrderStatusCodes
     * 
     * Gets all available order status codes
     * 
     * @return array Order status codes
     */
    public function getOrderStatusCodes()
    {
        \Errors::debugLogger(__METHOD__, 5);
        $this->sql = "
			SELECT orderStatusCode, orderStatusDescription
			FROM refOrderStatusCodes
			ORDER BY sortOrder, orderStatusCode ASC";
        return $this->DB->query($this->sql);
    }

    /**
     * getOrderCurrentStatusCode
     * 
     * Gets this orders current status code
     * 
     * @return array Order current status code
     */
    public function getOrderCurrentStatusCode()
    {
        \Errors::debugLogger(__METHOD__, 5);
        $this->sql     = "
			SELECT cop.orderStatusCode,
					cop.dateReported, cop.userIDReported,
					ref.orderStatusDescription
			FROM customerOrdersStatusHistory AS cop
				INNER JOIN refOrderStatusCodes AS ref
					ON cop.orderStatusCode = ref.orderStatusCode
			WHERE cop.orderID = :orderID
			ORDER BY cop.dateReported DESC
			LIMIT 1";
        $this->sqlData = array(':orderID' => $this->id);
        $res           = $this->DB->query($this->sql, $this->sqlData);
        if (!isset($res) || empty($res)) {
            //trigger_error('Unexpected results.', E_USER_ERROR);
            return false;
        }
        return $res[0];
    }

    /**
     * setOrderStatusCode
     * 
     * Sets order status code
     * 
     * @param int $customerID
     * @param int $orderID
     * @param string $statusCode
     * @param int $userIDReported Optional (null for system)
     * 
     * @return boolean
     */
    public function setOrderStatusCode($customerID, $orderID, $statusCode, $userIDReported = null)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        // Save Order Status History
        $this->sql     = "
			INSERT INTO customerOrdersStatusHistory
			(orderID, dateReported, userIDReported, orderStatusCode)
			VALUES
			(:orderID, :dateReported, :userIDReported, :orderStatusCode)";
        $this->sqlData = array(':orderID'         => $orderID,
            ':dateReported'    => Util::getDateTimeUTC(),
            ':userIDReported'  => $userIDReported,
            ':orderStatusCode' => $statusCode);
        $resA          = $this->DB->query($this->sql, $this->sqlData);
        if (!isset($resA)) {
            trigger_error('Unexpected results', E_USER_ERROR);
            return false;
        }

        // Update Order Status
        $this->sql = "
			UPDATE customerOrders
			SET orderStatusCode = :orderStatusCode";
        // Update Date Paid if setting to PD/CMP
        if (in_array($statusCode, array('PD', 'CMP'))) {
            $this->sql .= ", dateOrderPaid = :dateOrderPaid";
        }
        $this->sql .= "
			WHERE orderID = :orderID
            AND customerID = :customerID
			LIMIT 1";
        $this->sqlData = array(':orderID'         => $orderID,
            ':customerID'      => $customerID,
            ':orderStatusCode' => $statusCode);
        if (in_array($statusCode, array('PD', 'CMP'))) {
            $this->sqlData[':dateOrderPaid'] = Util::getDateTimeUTC();
        }
        $resB = $this->DB->query($this->sql, $this->sqlData);
        if (!isset($resB)) {
            trigger_error('Unexpected results', E_USER_ERROR);
            return false;
        }
        return true;
    }

    /**
     * setOrderDeliveryStatusCode
     * 
     * Updates order delivery status, if setting to Complete or Shipped then also update Grand Total
     * 
     * @param int $orderID
     * @param int $customerID
     * @param string $deliveryStatusCode
     * @return boolean
     */
    public function setOrderDeliveryStatusCode($customerID, $orderID, $deliveryStatusCode, $userIDReported)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);

        // History
        $this->sql     = "
			INSERT INTO customerOrdersDeliveryHistory
			(orderID, dateReported, userIDReported, deliveryStatusCode)
			VALUES
			(:orderID, :dateReported, :userIDReported, :deliveryStatusCode)";
        $this->sqlData = array(':orderID'            => $orderID,
            ':dateReported'       => Util::getDateTimeUTC(),
            ':userIDReported'     => $userIDReported,
            ':deliveryStatusCode' => $deliveryStatusCode);
        $res           = $this->DB->query($this->sql, $this->sqlData);
        if (!isset($res)) {
            trigger_error('Unexpected results.', E_USER_ERROR);
            return false;
        }

        // Order Delivery Status
        $this->sql     = "
			UPDATE customerOrders
			SET deliveryStatusCode = :deliveryStatusCode
			WHERE orderID = :orderID
            AND customerID = :customerID";
        $this->sqlData = array(':orderID'            => $orderID,
            ':customerID'         => $customerID,
            ':deliveryStatusCode' => $deliveryStatusCode);
        $res           = $this->DB->query($this->sql, $this->sqlData);
        if (!isset($res)) {
            trigger_error('Unexpected results.', E_USER_ERROR);
            return false;
        }
        return true;
    }

    /**
     * updateOrderStatusCode
     * 
     * Updates orders status code, if status is Paid then update Date Order Paid too
     * 
     * @return boolean
     * 
     * @todo parameters so cart can use it (see setOrderStatusCode)
     * 
     * @deprecated since version v0.0.1
     */
    public function updateOrderStatusCode()
    {
        \Errors::debugLogger(__METHOD__, 5);
        $san_orderStatusCode = trim($_POST['orderStatusCode']);

        // Save Order Status History
        $this->sql = "
			INSERT INTO customerOrdersStatusHistory
			(orderID, dateReported, userIDReported, orderStatusCode)
			VALUES
			(:orderID, :dateReported, :userIDReported, :orderStatusCode)";
        $sql_data  = array(':orderID'         => $this->id,
            ':dateReported'    => Util::getDateTimeUTC(),
            ':userIDReported'  => $_SESSION['userID'],
            ':orderStatusCode' => $san_orderStatusCode);
        $results   = $this->DB->query($this->sql, $sql_data);

        // Update Order Status
        $this->sql = "
			UPDATE customerOrders
			SET orderStatusCode = :orderStatusCode";

        // If Mark Paid
        if ($san_orderStatusCode == 'PD') {
            $this->sql .= ", dateOrderPaid = :dateOrderPaid";
            $sql_data = array(':orderID'         => $this->id,
                ':dateOrderPaid'   => Util::getDateTimeUTC(),
                ':orderStatusCode' => $san_orderStatusCode);
        } else {
            $sql_data = array(':orderID'         => $this->id,
                ':orderStatusCode' => $san_orderStatusCode);
        }
        $this->sql .= "
			WHERE orderID = :orderID
			LIMIT 1";
        return $this->DB->query($this->sql, $sql_data);
    }

    /**
     * getOrderDeliveryStatusCodes
     * 
     * Gets all available order delivery status codes
     * 
     * @return array Delivery status codes
     */
    public function getOrderDeliveryStatusCodes()
    {
        \Errors::debugLogger(__METHOD__, 5);
        $this->sql = "
			SELECT deliveryStatusCode, deliveryStatusDescription
			FROM refDeliveryStatusCodes
			ORDER BY sortOrder, deliveryStatusCode ASC";
        return $this->DB->query($this->sql);
    }

    /**
     * getOrderCurrentDeliveryStatusCode
     * 
     * Gets orders current delivery status code
     * 
     * @return array Delivery status code
     */
    public function getOrderCurrentDeliveryStatusCode()
    {
        \Errors::debugLogger(__METHOD__, 5);
        $this->sql     = "
			SELECT cod.deliveryStatusCode,
					cod.dateReported, cod.userIDReported,
					ref.deliveryStatusDescription
			FROM customerOrdersDeliveryHistory AS cod
				INNER JOIN refDeliveryStatusCodes AS ref
					ON cod.deliveryStatusCode = ref.deliveryStatusCode
			WHERE cod.orderID = :orderID
			ORDER BY cod.dateReported DESC
			LIMIT 1";
        $this->sqlData = array(':orderID' => $this->id);
        $res           = $this->DB->query($this->sql, $this->sqlData);
        if (!isset($res)) {
            trigger_error('Unexpected results.', E_USER_ERROR);
            return false;
        }
        if (empty($res[0])) {
            $res[] = array('deliveryStatusCode'        => 'CNC',
                'dateReported'              => '',
                'deliveryStatusDescription' => 'Cancelled');
        }
        return $res[0];
    }

    /**
     * getOrderProductCurrentDeliveryInfo
     * 
     * Gets order product current delivery information
     * 
     * @param int $productID
     * 
     * @return array Delivery information
     */
    public function getOrderProductCurrentDeliveryInfo($orderID, $productID)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql     = "
			SELECT cop.deliveryPrice,
					copd.deliveryStatusCode,
					copd.dateReported, copd.userIDReported,
					ref.deliveryStatusDescription
			FROM customerOrderProducts AS cop
				INNER JOIN customerOrderProductsDeliveryHistory AS copd
					ON (cop.orderID = copd.orderID
						AND cop.productID = copd.productID)
				INNER JOIN refDeliveryStatusCodes AS ref
					ON copd.deliveryStatusCode = ref.deliveryStatusCode
			WHERE copd.orderID = :orderID
				AND copd.productID = :productID
			ORDER BY copd.dateReported DESC
			LIMIT 1";
        $this->sqlData = array(':orderID'   => $orderID,
            ':productID' => $productID);
        $res           = $this->DB->query($this->sql, $this->sqlData);
        if (!isset($res)) {
            \Errors::debugLogger(__METHOD__, 5);
            trigger_error('Unexpected results.', E_USER_ERROR);
            return false;
        }
        return $res[0];
    }

    /**
     * getOrderProductsDeliveryReady
     * 
     * Checks if all products in order are shipped
     * 
     * @param int $orderID
     * 
     * @return boolean True if all products are shipped
     */
    public function getOrderProductsDeliveryReady($orderID)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        foreach (self::getOrderProducts($orderID) as $op) {
            $res          = self::getOrderProductCurrentDeliveryInfo($orderID, $op['productID']);
            $deliveryCode = $res['deliveryStatusCode'];
            if (!in_array($deliveryCode, array('SHP', 'CMP'))) {
                \Errors::debugLogger(__METHOD__ . ': false: oid:' . $orderID . ' pid:' . $op['productID'] . ' dsc:' . $deliveryCode);
                return false;
            }
        }
        \Errors::debugLogger(__METHOD__ . ':  true');
        return true;
    }

    /**
     * setOrderTotalPrice
     * 
     * Updates order total price
     * 
     * @param int $orderID
     * @param float $totalPrice
     * 
     * @return boolean
     */
    public function setOrderTotalPrice($orderID, $totalPrice)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql     = "
            UPDATE customerOrders
            SET totalOrderPrice = :totalOrderPrice
            WHERE orderID = :orderID
            LIMIT 1";
        $this->sqlData = array(':orderID'         => $orderID,
            ':totalOrderPrice' => $totalPrice);
        $res           = $this->DB->query($this->sql, $this->sqlData);
        if (!isset($res)) {
            trigger_error('Unexpected results.', E_USER_ERROR);
            return false;
        }
        return true;
    }

    /**
     * updateOrderProductDelivery
     * 
     * Updates delivery price and status of product in order
     * 
     * @return boolean
     */
    public function updateOrderProductDelivery()
    {
        \Errors::debugLogger(__METHOD__, 5);
        $san_order_productID                  = trim($_POST['order_productID']);
        $san_order_product_deliveryStatusCode = trim($_POST['order_product_deliveryStatusCode']);
        $san_order_product_deliveryPrice      = trim($_POST['order_product_deliveryPrice']);
        // Delivery Price
        $this->sql                            = "
			UPDATE customerOrderProducts
			SET deliveryPrice = :deliveryPrice
			WHERE orderID = :orderID
				AND productID = :productID
			LIMIT 1";
        $sql_data                             = array(':orderID'       => $this->id,
            ':productID'     => $san_order_productID,
            ':deliveryPrice' => $san_order_product_deliveryPrice);
        $results                              = $this->DB->query($this->sql, $sql_data);
        // Delivery Status
        $this->sql                            = "
			INSERT INTO customerOrderProductsDeliveryHistory
			(orderID, productID, dateReported, userIDReported, deliveryStatusCode)
			VALUES
			(:orderID, :productID, :dateReported, :userIDReported, :deliveryStatusCode)";
        $sql_data                             = array(':orderID'            => $this->id,
            ':productID'          => $san_order_productID,
            ':dateReported'       => Util::getDateTimeUTC(),
            ':userIDReported'     => $_SESSION['userID'],
            ':deliveryStatusCode' => $san_order_product_deliveryStatusCode);
        return $this->DB->query($this->sql, $sql_data);
    }

    /**
     * getOrderChangelog
     * 
     * Gets this orders changelog
     * 
     * @return array Changelog details
     */
    public function getOrderChangelog()
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql = "
			SELECT cosh.orderStatusCode, cosh.dateReported AS cosh_dateReported, cosh.userIDReported AS cosh_userIDReported,
							codh.deliveryStatusCode, codh.dateReported AS codh_dateReported, codh.userIDReported AS codh_userIDReported
			FROM customerOrdersStatusHistory AS cosh
					INNER JOIN customerOrdersDeliveryHistory AS codh
							ON cosh.orderID = codh.orderID
			WHERE cosh.orderID = :orderID
					OR codh.orderID = :orderID
			ORDER BY cosh.dateReported DESC,
									codh.dateReported DESC";

        $this->sql = "
SELECT * FROM (
			SELECT cosh.orderStatusCode AS code, 'cosh' AS history,
					cosh.dateReported AS dateReported, cosh.userIDReported AS userIDReported
			FROM customerOrdersStatusHistory AS cosh
			WHERE cosh.orderID = " . $this->id . "
			
			UNION

			SELECT codh.deliveryStatusCode AS code, 'codh' AS history,
					codh.dateReported AS dateReported, codh.userIDReported AS userIDReported
			FROM customerOrdersDeliveryHistory AS codh
			WHERE codh.orderID = " . $this->id . "

			ORDER BY dateReported DESC
) AS C
		";
        $sql_data  = array(':orderID' => $this->id);
        return $this->DB->query($this->sql, $sql_data);
    }

    /**
     * calcOrderProductTotal
     * 
     * Calculates the total price of all products in an order (pre-tax/shipping)
     * 
     * @uses getOrderProducts()
     * 
     * @param type $orderID Order ID
     * 
     * @return float Order total
     */
    public function calcOrderProductTotal($orderID)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $products = self::getOrderProducts($orderID);
        $amount   = 0;
        foreach ($products as $p) {
            $amount += ($p->qty * $p->price);
        }
        return Util::getFormattedNumber($amount);
    }

    /**
     * getOrderTotalAmount
     * 
     * Gets order total amount from database
     * 
     * @param int $orderID Order ID
     * 
     * @return boolean|float Total order price
     */
    public function getOrderTotalAmount($orderID)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql     = "
			SELECT totalOrderPrice
			FROM customerOrders
			WHERE orderID = :orderID
            LIMIT 1";
        $this->sqlData = array(':orderID' => $orderID);
        $res           = $this->DB->query($this->sql, $this->sqlData);
        if (!isset($res)) {
            trigger_error('Unexpected results.', E_USER_ERROR);
            return false;
        }
        return Util::getFormattedNumber($res[0]['totalOrderPrice']);
    }

    /**
     * getOrderTaxableAmount
     * 
     * Gets the orders taxable amount
     * 
     * @version v0.0.1
     * 
     * @since v0.0.1
     * 
     * @param int $orderID Order ID
     * 
     * @return float $taxable Taxable amount
     */
    public function getOrderTaxableAmount($orderID)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $products = self::getOrderProducts($orderID);
        $taxable  = 0;
        $product  = new Product();
        // Foreach product in order, Sum taxable total
        foreach ($products as $p) {
            $productSum       = 0;
            $productIsTaxable = $product->getProductTaxable($p['productID']);
            // Product is taxable
            if (!empty($productIsTaxable)) {
                $pInfo   = $product->getProductInfo($p['productID']);
                // Sum taxable options if selected
                $options = self::getOrderProductsOptions($orderID, $p['productID']);
                if (!empty($options)) {
                    foreach ($options as $option) {
                        $optionID      = $option['productOptionCustomID'];
                        $choiceID      = $option['productOptionChoiceCustomID'];
                        $optionDetails = $product->getProductOptionsChoicesCustom($optionID, $choiceID);
                        $detailPrice   = $optionDetails['choicePriceCustom'];
                        $productSum += $detailPrice;
                    }
                }
                $productSum += $pInfo['price'];
                $taxable += ($p['quantity'] * $productSum);
            }
        }
        // Taxable amount
        \Errors::debugLogger(__METHOD__ . ' Taxable amount: ' . $taxable, 5);
        return Util::getFormattedNumber($taxable);
    }

    /**
     * getOrderTaxRate
     * 
     * Get orders tax rate
     * 
     * @uses getOrderStateCode()
     * @uses getCartTaxRates()
     * 
     * @param int $cartID Cart ID
     * @param int $orderID Order ID
     * 
     * @return float Tax rate percentage
     */
    public function getOrderTaxRate($cartID, $orderID)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $taxRate = 0;

        // Order state code
        $orderStateCode = self::getOrderStateCode($orderID);

        // Carts tax rate for state code
        $cart = new Cart();
        foreach ($cart->getCartTaxRates($cartID) as $str) {
            if ($str['stateCode'] != $orderStateCode) {
                continue;
            }
            $taxRate = $str['stateTaxRate'];
            break;
        }
        \Errors::debugLogger(__METHOD__ . ' Carts state tax rate: ' . $taxRate, 5);
        return Util::getFormattedNumber($taxRate);
    }

    /**
     * getOrderStateCode
     * 
     * Gets the state code of the orders shipping address for taxes
     * 
     * @param type $orderID Order ID
     * 
     * @return string $orderStateCode State code
     */
    public function getOrderStateCode($orderID)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql     = "
            SELECT a.stateProvinceCounty
            FROM addresses AS a
                INNER JOIN customerOrders AS co
                    ON a.addressID = co.shippingAddressID
            WHERE co.orderID = :orderID
            LIMIT 1";
        $this->sqlData = array(':orderID' => $orderID);
        $res           = $this->DB->query($this->sql, $this->sqlData);
        if (!isset($res)) {
            trigger_error('Unexpected results.', E_USER_ERROR);
            return false;
        }
        $orderStateCode = $res[0]['stateProvinceCounty'];
        \Errors::debugLogger(__METHOD__ . ' Order state: ' . $orderStateCode, 5);
        return $orderStateCode;
    }

    /**
     * calcOrderTax
     * 
     * Calculates the total tax amount of a specified order ID
     * 
     * @uses getOrderTaxRate()
     * @uses getOrderTaxableAmount()
     * 
     * @param int $cartID Cart ID
     * @param int $orderID Order ID
     * 
     * @return float Total tax amount
     */
    public function calcOrderTax($cartID, $orderID)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $taxRate = self::getOrderTaxRate($cartID, $orderID);
        // Tax does not apply
        if (empty($taxRate)) {
            return 0;
        }
        // Tax applies, return tax amount
        $taxable  = self::getOrderTaxableAmount($orderID);
        $orderTax = $taxable * ($taxRate / 100);
        return Util::getFormattedNumber($orderTax);
    }

    public function calcOrderDelivery($orderID)
    {
        
    }

    /**
     * setOrderTotalTax
     * 
     * Updates order total tax
     * 
     * @param int $orderID
     * @param float $totalTax
     * 
     * @return boolean
     */
    public function setOrderTotalTax($orderID, $totalTax)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql     = "
            UPDATE customerOrders
            SET totalOrderTax = :totalOrderTax
            WHERE orderID = :orderID
            LIMIT 1";
        $this->sqlData = array(':orderID'       => $orderID,
            ':totalOrderTax' => $totalTax);
        $res           = $this->DB->query($this->sql, $this->sqlData);
        if (!isset($res)) {
            trigger_error('Unexpected results.', E_USER_ERROR);
            return false;
        }
        return true;
    }

    /**
     * setOrderTaxableAmount
     * 
     * Updates order total taxable amount
     * 
     * @param int $orderID
     * @param float $taxableAmount
     * 
     * @return boolean
     */
    public function setOrderTaxableAmount($orderID, $taxableAmount)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql     = "
            UPDATE customerOrders
            SET orderTaxableAmount = :taxableAmount
            WHERE orderID = :orderID
            LIMIT 1";
        $this->sqlData = array(':orderID'       => $orderID,
            ':taxableAmount' => $taxableAmount);
        $res           = $this->DB->query($this->sql, $this->sqlData);
        if (!isset($res)) {
            trigger_error('Unexpected results.', E_USER_ERROR);
            return false;
        }
        return true;
    }

    /**
     * setOrderTotalDelivery
     * 
     * Updates order total delivery
     * 
     * @param int $orderID
     * @param float $totalDelivery
     * 
     * @return boolean
     */
    public function setOrderTotalDelivery($orderID, $totalDelivery)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql     = "
            UPDATE customerOrders
            SET totalOrderDelivery = :totalOrderDelivery
            WHERE orderID = :orderID
            LIMIT 1";
        $this->sqlData = array(':orderID'            => $orderID,
            ':totalOrderDelivery' => $totalDelivery);
        $res           = $this->DB->query($this->sql, $this->sqlData);
        if (!isset($res)) {
            trigger_error('Unexpected results.', E_USER_ERROR);
            return false;
        }
        return true;
    }

    /**
     * orderProductsEmailAlerts
     * 
     * Checks each product in order for Notification Alerts to send
     * 
     * @version v0.0.1
     * 
     * @since v0.0.1
     * 
     * @param int $orderID Order ID
     * 
     * @return boolean
     */
    public function orderProductsEmailAlerts($orderID)
    {
        \Errors::debugLogger(__METHOD__, 5, true);
        \Errors::debugLogger(func_get_args(), 8, true);
        $master = array();
        foreach (self::getOrderProducts($orderID) as $p) {
            if (!empty($p['productEmail'])) {
                $master[$p['productEmail']][] = array('sku' => $p['productSKU'], 'name' => $p['productName'], 'qty'  => $p['quantity']);
            }
        }
        if (!empty($master)) {
            $subject = 'New Order Product Alert: (Order #' . $orderID . ')';
            foreach ($master as $e => $v) {
                $body = '<p>A new order has been placed for the following product(s).</p>
                    <p>Note: Check on the order payment status before processing the order to ensure payment has been fulfilled.</p><hr />';
                foreach ($v as $i) {
                    if (!empty($i['productSKU'])) {
                        $body .= '
                            <b>SKU:</b> ' . $i['productSKU'] . '<br />';
                    }
                    $body .= '
                        <b>Name:</b> ' . $i['name'] . '<br />
                        <b>Quantity:</b> ' . $i['qty'] . '<hr />';
                }
                $body .= "<p>View order online: <a href='" . cartPublicUrl . "admin?p=order&a=view_customer_order_single&s=2&oid=" . $orderID . "'>Order #" . $orderID . "</a>";
                $from    = $e;
                $replyto = $e;
                Email::sendEmail($e, $from, $replyto, NULL, NULL, $subject, $body);
            }
        }
    }

}
?>

