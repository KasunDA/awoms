<?php
namespace killerCart;

/**
 * Payment Gateway class
 *
 * Payment Gateway cart management methods
 *
 * PHP version 5
 *
 * @category  Cart
 * @package   Cart
 * @author    Brock Hensley <brock@brockhensley.com>
 * @version   v0.0.1
 * @since     v0.0.1
 */
class PaymentGateway extends Cart
{
    /**
     * getPaymentGatewayByID
     * 
     * Gets payment gateway info from database by ID
     * 
     * @param int $id Payment gateway ID
     * 
     * @return array|boolean Payment gateway information from database
     */
    public function getPaymentGatewayByID($id)
    {
        $this->sql     = "
            SELECT gatewayName, gatewayURL, gatewayUsername, gatewayPassphrase, gatewayNotes, gatewayTemplate, gatewayOffline
            FROM paymentGateways
            WHERE gatewayID = :gatewayID
            LIMIT 1";
        $this->sqlData = array(':gatewayID' => $id);
        $res           = $this->DB->query($this->sql, $this->sqlData);
        if (!isset($res)) {
            \Errors::debugLogger(__METHOD__, 5);
            trigger_error('Unexpected results', E_USER_ERROR);
            return false;
        }
        return $res[0];
    }

    /**
     * setCartsActivePaymentGateway
     * 
     * Sets carts active payment gateway
     * 
     * @param int $cartID CartID
     * @param int $pgID Payment Gateway ID to make Active
     * 
     * @return boolean
     */
    public function setCartsActivePaymentGateway($cartID, $pgID)
    {
        \Errors::debugLogger(__METHOD__ . ': cartID:' . $cartID . ' pgID:' . $pgID);
        // Set each of carts PGs to Inactive first
        $this->sql     = "
            UPDATE cartsPaymentGateways
            SET gatewayActive = 0
            WHERE cartID = :cartID";
        $this->sqlData = array(':cartID' => $cartID);
        $resi          = $this->DB->query($this->sql, $this->sqlData);
        if (!isset($resi)) {
            trigger_error('Unexpected results', E_USER_ERROR);
            return false;
        }
        // Activate the selected PG
        $this->sql     = "
            UPDATE cartsPaymentGateways
            SET gatewayActive = 1
            WHERE cartID = :cartID
            AND gatewayID = :gatewayID";
        $this->sqlData = array(':cartID'   => $cartID, ':gatewayID' => $pgID);
        $res           = $this->DB->query($this->sql, $this->sqlData);
        if (!isset($res)) {
            trigger_error('Unexpected results', E_USER_ERROR);
            return false;
        }
        return true;
    }

    /**
     * getCartsActivePaymentGatewayID
     * 
     * Gets the carts active payment gateway ID
     * 
     * @param int $cartID Cart ID
     * 
     * @return boolean|int Payment gateway ID actively used by cart
     */
    public function getCartsActivePaymentGatewayID($cartID)
    {
        \Errors::debugLogger(__METHOD__, 5);
        $this->sql     = "
            SELECT gatewayID
            FROM cartsPaymentGateways
            WHERE cartID = :cartID
            AND gatewayActive = :gatewayActive
            LIMIT 1";
        $this->sqlData = array(':cartID'       => $cartID, ':gatewayActive' => 1);
        $res           = $this->DB->query($this->sql, $this->sqlData);
        if (!isset($res)) {
            trigger_error('Unexpected results', E_USER_ERROR);
            return false;
        }
        if (empty($res)) {
            return false;
        }
        return $res[0]['gatewayID'];
    }

    /**
     * getCartsPaymentGateways
     * 
     * Gets list of all available payment gateways for selected cart
     * 
     * @param int $cartID Cart ID
     * 
     * @return boolean|array Payment Gateway data
     */
    public function getCartsPaymentGateways($cartID)
    {
        \Errors::debugLogger(__METHOD__, 5);
        $this->sql     = "
            SELECT pg.gatewayID, pg.gatewayName, pg.gatewayURL, pg.gatewayUsername, pg.gatewayPassphrase, pg.gatewayNotes
            FROM paymentGateways AS pg
                INNER JOIN cartsPaymentGateways AS spg
                    ON pg.gatewayID = spg.gatewayID
            WHERE spg.cartID = :cartID";
        $this->sqlData = array(':cartID' => $cartID);
        $res           = $this->DB->query($this->sql, $this->sqlData);
        if (!isset($res)) {
            trigger_error('Unexpected results', E_USER_ERROR);
            return false;
        }
        return $res;
    }

    /**
     * savePaymentGateway
     * 
     * Creates new or saves existing payment gateway
     * 
     * @param string $cartID
     * @param string $name
     * @param string $url
     * @param string $username
     * @param string $passphrase
     * @param string $notes
     * @param string $template
     * @param boolean $isOffline
     * @param boolean|int $pgID
     * 
     * @return boolean
     */
    public function savePaymentGateway($cartID, $name, $url, $username, $passphrase, $notes, $template, $isOffline, $pgID = null)
    {
        \Errors::debugLogger(__METHOD__, 5);
        // New or Existing
        if (empty($pgID)) {
            $pgID = 'DEFAULT';
        }
        // Offline or Online
        if (empty($isOffline)) {
            $isOffline = 'NULL';
        }
        // Save payment gateway
        $this->sql     = "
            INSERT INTO paymentGateways
                (gatewayID, gatewayName, gatewayURL, gatewayUsername, gatewayPassphrase, gatewayNotes, gatewayTemplate, gatewayOffline)
            VALUES
                (:gatewayID, :gatewayName, :gatewayURL, :gatewayUsername, :gatewayPassphrase, :gatewayNotes, :gatewayTemplate, :gatewayOffline)
            ON DUPLICATE KEY UPDATE
                gatewayID = :gatewayID,
                gatewayName = :gatewayName,
                gatewayURL = :gatewayURL,
                gatewayUsername = :gatewayUsername,
                gatewayPassphrase = :gatewayPassphrase,
                gatewayNotes = :gatewayNotes,
                gatewayTemplate = :gatewayTemplate,
                gatewayOffline = :gatewayOffline";
        $this->sqlData = array(':gatewayID'         => $pgID,
            ':gatewayName'       => $name,
            ':gatewayURL'        => $url,
            ':gatewayUsername'   => $username,
            ':gatewayPassphrase' => $passphrase,
            ':gatewayNotes'      => $notes,
            ':gatewayTemplate'   => $template,
            ':gatewayOffline'    => $isOffline);
        $resID         = $this->DB->query($this->sql, $this->sqlData);
        if (!isset($resID)) {
            trigger_error('Unexpected results.', E_USER_ERROR);
            return false;
        }

        // Associate cart ID and pg ID
        $this->sql     = "
            INSERT INTO cartsPaymentGateways
            (cartID, gatewayID, gatewayActive)
            VALUES
            (:cartID, :gatewayID, :gatewayActive)";
        $this->sqlData = array(':cartID'       => $cartID,
            ':gatewayID'     => $resID,
            ':gatewayActive' => 0);
        $res           = $this->DB->query($this->sql, $this->sqlData);
        if (!isset($res)) {
            trigger_error('Unexpected results.', E_USER_ERROR);
            return false;
        }
        return $resID;
    }

}
?>
