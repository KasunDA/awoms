<?php
namespace killerCart;

/*
 * Customer class
 * https://github.com/jkshay/Cart/wiki/Customer
 */
class Customer extends Cart
{
    /*
     * __construct
     */
    public function __construct()
    {
        $this->DB = new \Database();
    }

    /**
     * getCustomers
     * 
     * Returns list of customers of selected cart
     * 
     * @param int $cartID Cart ID
     * 
     * @return array
     */
    public function getCustomers($cartID)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        if ($cartID == NULL) {
            $whereSql = "IS NOT NULL";
        } else {
            $whereSql = "= " . $cartID;
        }
        $this->sql = "
			SELECT customerID, customCustomerID
			FROM customers
			WHERE cartID " . $whereSql . "
            ORDER BY customerID DESC";
        return $this->DB->query($this->sql);
    }

    /**
     * getCustomerInfo
     * 
     * Returns array of customer information
     * 
     * @version v1.1.12
     * @since v1.0.3
     * 
     * @param int $customerID Customer ID
     * 
     * @return array 
     */
    public function getCustomerInfo($customerID)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql     = "
			SELECT ci.customerID, ci.companyName, ci.firstName, ci.middleName, ci.lastName, ci.phone, ci.email, ci.notes, ci.fingerprint, ci.visitorIP, ci.username, ci.passphrase, ci.regDate,
                ci.lastLoginDate, ci.lastLoginIP, ci.lastFailedLoginDate, ci.lastFailedLoginIP, ci.failedLoginAttempts, ci.protectedPrivateKey, ci.loginAllowed,
                c.customCustomerID
			FROM customerInfo AS ci
                INNER JOIN customers AS c
                    ON ci.customerID = c.customerID
			WHERE ci.customerID = :customerID";
        $this->sqlData = array(':customerID' => $customerID);
        $res           = $this->DB->query($this->sql, $this->sqlData);
        return $res[0];
    }

    /**
     * doesCustomerExistByEmail
     * 
     * Gets count of customers with matching email address
     * 
     * @since v1.0.3
     * 
     * @param string $email Email
     * 
     * @return int Count
     */
    public function doesCustomerExistByEmail($email)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql     = "
			SELECT COUNT('customerID') AS Total
			FROM customerInfo
			WHERE email = :email";
        $this->sqlData = array(':email' => $email);
        $res           = $this->DB->query($this->sql, $this->sqlData);
        return $res[0]['Total'];
    }

    /**
     * doesCustomerExistByUsername
     * 
     * Gets count of customers with matching username
     * 
     * @since v1.1.9
     * 
     * @param string $username Username
     * 
     * @return int Count
     */
    public function doesCustomerExistByUsername($username)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql     = "
			SELECT COUNT('customerID') AS Total
			FROM customerInfo
			WHERE username = :username";
        $this->sqlData = array(':username' => $username);
        $res           = $this->DB->query($this->sql, $this->sqlData);
        return $res[0]['Total'];
    }

    /**
     * getCustomerIDByUsername
     * 
     * Gets customer ID with matching username
     * 
     * @since v1.1.9
     * 
     * @param string $username Username
     * 
     * @return int Customer ID
     */
    public function getCustomerIDByUsername($username)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql     = "
			SELECT customerID
			FROM customerInfo
			WHERE username = :username";
        $this->sqlData = array(':username' => $username);
        $res           = $this->DB->query($this->sql, $this->sqlData);
        if (empty($res)) {
            return false;
        } else {
            return $res[0]['customerID'];
        }
    }

    /**
     * createCustomer
     * 
     * Creates new customer
     * 
     * @since v1.0.3
     * 
     * @param int $cartID
     * @param int $groupID
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     * @param string $username
     * @param string $passphrase
     * @param string $notes
     * @param int $loginAllowed
     * 
     * @return boolean|int Customer ID
     */
    public function createCustomer($cartID, $groupID, $firstName, $lastName, $email, $username, $passphrase, $notes, $loginAllowed)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        // Create Customer ID
        $this->sql     = "
            INSERT INTO customers
                (cartID)
            VALUES
                (:cartID)";
        $this->sqlData = array(':cartID' => $cartID);
        $r             = $this->DB->query($this->sql, $this->sqlData);
        if (!empty($r)) {
            $customerID = $r;
            \Errors::debugLogger(__METHOD__ . ' Created Customer ID: ' . $customerID, 8);
        } else {
            \Errors::debugLogger(__METHOD__ . ' Failed to Create Customer ID', 8);
            return false;
        }
        // Save Customer Info
        $user             = new User();
        $hashedPassphrase = $user->getPassphraseHash($passphrase);
        $this->sql        = "
            INSERT INTO customerInfo
                (customerID, firstName, lastName, email, notes, username, passphrase, regDate, loginAllowed)
            VALUES
                (:customerID, :firstName, :lastName, :email, :notes, :username, :passphrase, :regDate, :loginAllowed)";
        $this->sqlData    = array(
            ':customerID'   => $customerID,
            ':firstName'    => $firstName,
            ':lastName'     => $lastName,
            ':email'        => $email,
            ':notes'        => $notes,
            ':username'     => $username,
            ':passphrase'   => $hashedPassphrase,
            ':regDate'      => Util::getDateTimeUTC(),
            ':loginAllowed' => $loginAllowed);
        $ci               = $this->DB->query($this->sql, $this->sqlData);
        if (!isset($ci)) {
            return false;
        } else {
            return $customerID;
        }
    }

    /**
     * updateCustomerInfo
     * 
     * Saves new customer info
     * 
     * @since v1.0.3
     * 
     * @param int $customerID CustomerID
     * @param string $companyName Company Name
     * @param string $firstName Firstname
     * @param string $middleName Middlename
     * @param string $lastName Lastname
     * @param string $phone Phone
     * @param string $email Email
     * @param string $notes Notes
     * @param string $username (Optional) Username
     * @param string $passphrase (Optional) Passphrase
     * @param int $loginAllowed (Optional) Login Allowed 1=enabled, 0=disabled
     * 
     * @return boolean
     */
    public function updateCustomerInfo($customerID, $companyName, $firstName, $middleName, $lastName, $phone, $email, $notes,
                                       $username, $passphrase, $loginAllowed)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql     = "
            UPDATE customerInfo
            SET companyName = :companyName, firstName = :firstName, middleName = :middleName, lastName = :lastName, phone = :phone, email = :email, notes = :notes";
        $this->sqlData = array(':companyName' => $companyName,
            ':customerID'  => $customerID,
            ':firstName'   => $firstName,
            ':middleName'  => $middleName,
            ':lastName'    => $lastName,
            ':phone'       => $phone,
            ':email'       => $email,
            ':notes'       => $notes);
        if (!empty($username)) {
            $this->sql .= ", username = :username";
            $this->sqlData[':username'] = $username;
        }
        if (!empty($passphrase)) {
            \Errors::debugLogger(__METHOD__ . ' New Passphrase');
            $u                            = new User();
            $hashedPassphrase             = $u->getPassphraseHash($passphrase);
            $this->sql .= ", passphrase = :passphrase";
            $this->sqlData[':passphrase'] = $hashedPassphrase;
        }
        if (in_array($loginAllowed, array('0', '1'))) {
            $this->sql .= ", loginAllowed = :loginAllowed";
            $this->sqlData[':loginAllowed'] = $loginAllowed;
        }
        $this->sql .= "
            WHERE customerID = :customerID
            LIMIT 1";
        return $this->DB->query($this->sql, $this->sqlData);
    }

    /**
     * updateCustomerLogin
     * 
     * Updates username and passphrase for customer
     * 
     * @since v1.0.3
     * 
     * @uses User\getPassphraseHash()
     * 
     * @param int $customerID Customer ID to change
     * @param string $username Username to set
     * @param string $passphrase Password to set
     * @param boolean $loginAllowed True/False allowed to login
     * 
     * @return boolean
     */
    public function updateCustomerLogin($customerID, $username, $passphrase, $loginAllowed)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $u                = new User();
        $hashedPassphrase = $u->getPassphraseHash($passphrase);
        if (!empty($loginAllowed)) {
            $loginAllowed = 1;
        } else {
            $loginAllowed = 0;
        }
        $this->sql     = "
            UPDATE customerInfo
            SET username = :username, passphrase = :passphrase, loginAllowed = :loginAllowed";
        $this->sql .= "
            WHERE customerID = :customerID
            LIMIT 1";
        $this->sqlData = array(':customerID'   => $customerID,
            ':username'     => $username,
            ':passphrase'   => $hashedPassphrase,
            ':loginAllowed' => $loginAllowed);
        return $this->DB->query($this->sql, $this->sqlData);
    }

    /**
     * getCustomerAddresses
     * 
     * Returns array of customer addresses
     * 
     * @version v1.0.3
     * 
     * @param int $customerID Customer ID
     * @param int $addressID Optional Address ID to narrow results to
     * 
     * @return boolean|array
     */
    public function getCustomerAddresses($customerID, $addressID = NULL)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql     = "
			SELECT ca.addressID, ca.addressTypeCode, ca.dateFrom, ca.dateTo,
                a.addressID, a.firstName, a.middleName, a.lastName, a.phone, a.email, a.line1, a.line2, a.line3, a.city, a.zipPostcode,
                a.stateProvinceCounty, a.country, a.addressNotes
			FROM customerAddresses AS ca
                INNER JOIN addresses AS a
                    ON ca.addressID = a.addressID
			WHERE ca.customerID = :customerID";
        $this->sqlData = array(':customerID' => $customerID);
        if (!empty($addressID)) {
            $this->sql .= " AND ca.addressID = :addressID";
            $this->sqlData[':addressID'] = $addressID;
        }
        $r = $this->DB->query($this->sql, $this->sqlData);
        if (!empty($addressID)) {
            return $r[0];
        } else {
            return $r;
        }
    }

    /**
     * getCustomerPaymentMethods
     * 
     * Returns all of customers payment methods info
     * 
     * @since v1.0.3
     * 
     * @param int $customerID Customer ID
     * 
     * @return array Payment method info
     */
    public function getCustomerPaymentMethods($customerID)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql     = "
			SELECT cpm.paymentMethodID, cpm.paymentMethodCode, cpm.billingAddressID,
                pm.number1, pm.number2, pm.number3, pm.expMonth, pm.expYear, pm.paymentMethodNotes
            FROM customerPaymentMethods AS cpm
                INNER JOIN paymentMethods AS pm
                    ON cpm.paymentMethodID = pm.paymentMethodID
			WHERE cpm.customerID = :customerID";
        $this->sqlData = array(':customerID' => $customerID);
        return $this->DB->query($this->sql, $this->sqlData);
    }

    /**
     * changeCustomerUserPassphrase
     * 
     * @since v1.0.3
     * 
     * @param int $customerID
     * @param string $passphrase
     * 
     * @uses getPassphraseHash()
     * 
     * @return boolean
     */
    public function changeCustomerUserPassphrase($customerID, $passphrase)
    {
        \Errors::debugLogger(__METHOD__, 5);
        $u                = new User();
        $hashedPassphrase = $u->getPassphraseHash($passphrase);
        $this->sql        = "
            UPDATE customerInfo
            SET passphrase = :passphrase
            WHERE customerID = :customerID";
        $sqlData          = array(':customerID' => $customerID,
            ':passphrase' => $hashedPassphrase);
        $results          = $this->DB->query($this->sql, $sqlData);
        if (!isset($results)) {
            trigger_error('Unable to change passphrase!', E_USER_ERROR);
            return false;
        }
        return true;
    }

    /**
     * getCustomerOrders
     * 
     * Returns list of specified customers orders
     * 
     * @param int $customerID Customer ID
     * 
     * @return array
     */
    public function getCustomerOrders($customerID)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql     = "
			SELECT orderID, customerID, paymentMethodID, orderStatusCode, dateOrderPlaced, dateOrderPaid, totalOrderPrice, orderNotes
			FROM customerOrders
			WHERE customerID = :customerID
            ORDER BY dateOrderPlaced DESC";
        $this->sqlData = array(':customerID' => $customerID);
        return $this->DB->query($this->sql, $this->sqlData);
    }

}
?>