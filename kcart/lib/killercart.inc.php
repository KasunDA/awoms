<?php
namespace killerCart;

/**
 * KillerCart class
 * 
 * KillerCart management methods including checkout process
 * 
 * @category   KillerCart
 * @package    KillerCart
 * @author     Brock Hensley <brock@brockhensley.com>
 * @version    v2.0.1
 * @since      v0.0.1
 */
class KillerCart
{
    /**
     * @var PDO SQL conneciton
     * @var string SQL statement
     * @var string SQL data
     */
    protected $DB, $sql, $sqlData;

    /**
     * @var array Class data
     */
    protected $data = array();

    /**
     * Main magic methods
     *
     * @param int $cartID Cart ID
     * 
     * @return boolean
     * 
     * @throws trigger_error
     * @access public
     */
    public function __construct($cartID = false)
    {
        \Errors::debugLogger(__METHOD__, 10);
        \Errors::debugLogger(func_get_args(), 10);
        if (empty($cartID)) {
            trigger_error("1005 - Missing cart ID", E_USER_ERROR);
            return false;
        }

        echo str_replace("/home/dirt/Projects/AWOMS","",__FILE__).':'.__LINE__.'@'.time().'=LookupDomainBrand<BR/>';
        $load = \Bootstrap::lookupDomainBrand();

        echo str_replace("/home/dirt/Projects/AWOMS","",__FILE__).':'.__LINE__.'@'.time().'=NewSession<BR/>';
        $s = new \Session();

        echo str_replace("/home/dirt/Projects/AWOMS","",__FILE__).':'.__LINE__.'@'.time().'=Session[data]:<BR/>';
        $ts = $s->data['session'];
        var_dump($ts);

        echo str_replace("/home/dirt/Projects/AWOMS","",__FILE__).':'.__LINE__.'@'.time().'=preThis()<BR/>';
        var_dump($this);

        $this->data['session'] = $ts;
        $this->data['session']['cartID'] = $cartID;
        $this->DB                         = new \Database();

        echo str_replace("/home/dirt/Projects/AWOMS","",__FILE__).':'.__LINE__.'@'.time().'=postThis()<BR/>';
        var_dump($this);

        //$this->doIKnowYou();
    }

    public function __destruct()
    {
        unset($this->sql, $this->sqlData);
    }

    public function __set($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function __get($key)
    {
        if ($this->__isset($key)) {
            return $this->data[$key];
        }
        return false;
    }

    public function __isset($key)
    {
        if (array_key_exists($key, $this->data)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * doIKnowYou
     *
     * Triggers start new or resume existing session based on client cookie
     *
     * @version v0.0.1
     * 
     * @uses resumeSession
     * @uses startSession
     * @return boolean True on existing session, False on unknown
     */
    private function doIKnowYou()
    {
        \Errors::debugLogger(__METHOD__, 10);
        if (isset($_SERVER['HTTP_COOKIE'])
                && (!empty($_COOKIE[cartCodeNamespace])
                        || !empty($_COOKIE[cartCodeNamespace."Admin"])
                        || !empty($_COOKIE[BRAND_LABEL]))) {
            
            if (!empty($_COOKIE[cartCodeNamespace]))
            {
                $cook = $_COOKIE[cartCodeNamespace];
            } elseif (!empty($_COOKIE[cartCodeNamespace."Admin"])) {
                $cook = $_COOKIE[cartCodeNamespace."Admin"];
            } elseif (!empty($_COOKIE[BRAND_LABEL])) {
                $cook = $_COOKIE[BRAND_LABEL];
            } else {
                $cook = FALSE;
            }
            \Errors::debugLogger(__METHOD__ . ':  Cookie found: ' . $cook, 10);
            if ($this->validateVisitor($cook)) {
                \Errors::debugLogger(__METHOD__ . ':  Yes! Welcome back! Resuming session...', 10);
                $this->resumeSession();
                return true;
            }
        } else {
            \Errors::debugLogger(__METHOD__ . ':  No cookie found...'.serialize($_COOKIE), 10);
        }
        \Errors::debugLogger(__METHOD__ . ':  No. Hello! Starting session...', 10);
        $this->startNewSession();
        return false;
    }

    /**
     * startNewSession
     *
     * Starts new session for new visitor
     *
     * @uses makeFingerprint
     * @uses setCookie
     * @uses saveSession
     * @return boolean
     */
    private function startNewSession()
    {
        \Errors::debugLogger(__METHOD__, 10);
        // Make new fingerprint for visitor
        $this->makeFingerprint();
        // Set cookie with new set/expiration dates
        $this->setCookie();
        // Save visitor information to database, associated with cookie id/fingerprint		
        $this->saveSession();
        return true;
    }

    /**
     * saveSession
     *
     * Saves session data to database
     *
     * @return boolean
     *
     * @deprecated since version v2
     */
    public function saveSession()
    {
        return;

        \Errors::debugLogger(__METHOD__, 10);
        \Errors::debugLogger(func_get_args(), 10);
        \Errors::debugLogger(__METHOD__.' fingerprint: '.$this->data['session']['fingerprint'], 10);
        $this->sql = "INSERT INTO `sessions`
					(`fingerprint`, `cartID`, `setTime`, `expiresTime`, `visitorIP`, `session`)
					VALUES
					(:fingerprint, :cartID, :setTime, :expiresTime, :visitorIP, :session)
					ON DUPLICATE KEY UPDATE
						`fingerprint` = :fingerprint,
						`cartID` = :cartID,
						`setTime` = :setTime,
						`expiresTime` = :expiresTime,
						`visitorIP` = :visitorIP,
						`session` = :session";
        $sql_data  = array(':fingerprint' => $this->data['session']['fingerprint'],
            ':cartID'     => $this->data['session']['cartID'],
            ':setTime'     => $this->data['session']['setTime'],
            ':expiresTime' => $this->data['session']['expiresTime'],
            ':visitorIP'   => $this->data['session']['visitorIP'],
            ':session'        => $this->encrypt($this->data['session']['expiresTime'], $this->data['session']['fingerprint'],
                                             serialize($this->data)));
        $results   = $this->DB->query($this->sql, $sql_data);
        if (!isset($results)) {
            \Errors::debugLogger(__METHOD__ . ':  *ERROR* Query:' . PHP_EOL . '--------------------' . PHP_EOL . $this->sql . PHP_EOL . '--------------------');
            trigger_error("Error #C000 (save_session): Invalid results.", E_USER_ERROR);
            return false;
        }
        return true;
    }

    /**
     * resumeSession
     *
     * Resumes existing session data from database
     *
     * @uses decrypt
     * @return boolean
     *
     * @deprecated
     */
    private function resumeSession()
    {
        return;

        \Errors::debugLogger(__METHOD__, 10);
        \Errors::debugLogger(func_get_args(), 10);
        $this->sql = "
			SELECT `expiresTime`, `session`
			FROM `sessions`
			WHERE `fingerprint` = :fingerprint
            AND (`brandID` = :brandID OR `cartID` = :cartID)";
        $sql_data  = array(':cartID'     => $this->data['session']['cartID'],
            ':fingerprint' => $this->data['session']['fingerprint'],
            ':brandID' => BRAND_ID);
        $results   = $this->DB->query($this->sql, $sql_data);
        if (empty($results)) {
            trigger_error("Error #C000 (resume_session): Invalid results.", E_USER_ERROR);
            return false;
        }
        
        $expires = $results[0]['expiresTime'];
        $session = $results[0]['session'];
        
        $this->data['session'] = unserialize($this->decrypt($expires, $this->data['session']['fingerprint'], $session));
        
        // Set current theme, cart ID (?)
        $this->data['session']['cartID'] = CART_ID;
        
        $this->data['session']['cartTheme'] = self::getCartTheme();
        return true;
    }

    /**
     * validateVisitor
     *
     * Validates current visitor information against database to ensure session is not being hijacked
     *
     * @version v0.0.1
     * 
     * @uses getVisitorIP
     * @return boolean
     * @todo expand validation to browser fingerprint etc.
     */
    private function validateVisitor($fingerprint)
    {
        \Errors::debugLogger(__METHOD__, 10);
        \Errors::debugLogger(func_get_args(), 10);
        $this->sql = "
			SELECT cartID, setTime, expiresTime, visitorIP
			FROM sessions
			WHERE fingerprint = :fingerprint";
        $sql_data  = array(':fingerprint' => $fingerprint);
        $results   = $this->DB->query($this->sql, $sql_data);
        if (!empty($results)) {
            $this->getVisitorIP();
            \Errors::debugLogger(__METHOD__ . ':  Comparing get_visitorIP (' . $this->data['session']['visitorIP'] . ') with DB (' . $results[0]['visitorIP'] . ')',
                               10);
            if ($this->data['session']['visitorIP'] == $results[0]['visitorIP']) {
                \Errors::debugLogger(__METHOD__ . ':  Visitor IP matches what we cartd from last session!', 10);
                // Visitor Validated
                $this->data['session']['fingerprint'] = $fingerprint;
                return true;
            } else {
                \Errors::debugLogger(__METHOD__ . ':  Visitor IP does NOT match what we cartd from last session! *** ALERT ***: ' . $this->sql);
            }
        } else {
            \Errors::debugLogger(__METHOD__ . ':  Cant find session: ' . $cook);
        }
        // Visitor is not recognized
        return false;
    }

    /**
     * makeFingerprint
     *
     * Generates unique fingerprint for new visitor
     *
     * @uses getVisitorIP
     * @return string Visitor unique fingerprint
     */
    private function makeFingerprint()
    {
        \Errors::debugLogger(__METHOD__, 10);
        $this->getVisitorIP();
        $this->data['session']['fingerprint'] = hash('sha512',
                                                    $this->data['session']['visitorIP'] + uniqid(mt_rand(1, mt_getrandmax()),
                                                                                                                true));
        \Errors::debugLogger(__METHOD__.' fingerprint: '.$this->data['session']['fingerprint'], 10);
        return $this->data['session']['fingerprint'];
    }

    /**
     * getVisitorIP
     *
     * Gets visitor IP address
     *
     * @return string Visitor IP address
     */
    private function getVisitorIP()
    {
        \Errors::debugLogger(__METHOD__, 10);
        $ip = $_SERVER['REMOTE_ADDR'];
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        return $this->data['session']['visitorIP'] = $ip;
    }

    /**
     * setCookie
     *
     * Saves cookie with session identifier on visitor browser
     * 
     * @version v0.0.1
     *
     * @return boolean
     * 
     * @todo Regenerate fingerprint
     * @todo domain
     *
     * @deprecated
     */
    private function setCookie()
    {
        return;


        \Errors::debugLogger(__METHOD__, 1, true);
        $this->data['session']['name']        = cartCodeNamespace;
        $this->data['session']['setTime']     = time();
        $this->data['session']['expiresTime'] = $this->data['session']['setTime'] + (60 * 60 * 24 * 365);
        $this->data['session']['cartTheme']  = self::getCartTheme();
        
        if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) {
            $this->data['session']['https'] = 1;
        } else {
            $this->data['session']['https'] = 0;
        }

        $httponly = true; // This stops javascript being able to access the session id.
        $domain = ".".$_SERVER['HTTP_HOST'];
        \Errors::debugLogger(__METHOD__.' fingerprint: '.$this->data['session']['fingerprint'], 1, true);
        if (!setcookie($this->data['session']['name'],
                $this->data['session']['fingerprint'],
                $this->data['session']['expiresTime'],
                '/',
                $domain, $this->data['session']['https'],
                $httponly)
                ) {
            \Errors::debugLogger(__METHOD__ . ':  ***** FAIL ***** ' . serialize($this->data['session']));
            trigger_error("Error #C000 (set_cookie): Invalid results.", E_USER_ERROR);
            return false;
        }
        return true;
    }

    /**
     * removeCookies
     *
     * Removes all cart cookies from visitor browser
     *
     * @version v0.0.1
     * 
     * @return boolean
     */
    public function removeCookies()
    {
        \Errors::debugLogger(__METHOD__, 10);
        if (isset($_SERVER['HTTP_COOKIE'])) {
            $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
            foreach ($cookies as $cookie) {
                $parts = explode('=', $cookie);
                $name  = trim($parts[0]);
                if ($name != cartCodeNamespace || $name != cartCodeNamespace."Admin") {
                    continue;
                }
                setcookie($name, '', time() - 1000);
                setcookie($name, '', time() - 1000, '/');
                \Errors::debugLogger(__METHOD__ . ':  Removing cookie ' . $name);
            }
        }
        return true;
    }

    /**
     * encrypt
     *
     * @param string $key
     * @param string $authKey
     * @param string $plain
     * @return string Encrypted $plain
     */
    function encrypt($key, $authKey, $plain)
    {
        \Errors::debugLogger(__METHOD__, 9);
        \Errors::debugLogger(func_get_args(), 9);
        $size       = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CFB);
        $iv         = mcrypt_create_iv($size, MCRYPT_DEV_URANDOM);
        $cipherText = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $plain, MCRYPT_MODE_CFB, $iv);
        $auth       = hash_hmac('sha512', $cipherText, $authKey, true);
        return $encrypted  = base64_encode($iv . $cipherText . $auth);
    }

    /**
     * decrypt
     *
     * @param string $key
     * @param string $authKey
     * @param string $encrypted
     * @return string Decrypted $encrypted
     */
    function decrypt($key, $authKey, $encrypted)
    {
        \Errors::debugLogger(__METHOD__, 9);
        \Errors::debugLogger(func_get_args(), 9);
        $size       = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CFB);
        $encrypted  = base64_decode($encrypted);
        $iv         = substr($encrypted, 0, $size);
        $auth       = substr($encrypted, -64);
        $cipherText = substr($encrypted, $size, -64);
        if ($auth != hash_hmac('sha512', $cipherText, $authKey, true)) {
            return false;
        }
        return $plainText = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $cipherText, MCRYPT_MODE_CFB, $iv);
    }














    /**
     * updateProductQtyInCart
     *
     * Updates product quantity in cart
     * 
     * @version v0.0.1
     * 
     * @uses removeProductFromCart() if quantity is 0
     * 
     * @param int $productID Product ID
     * @param int $quantity Quantity to set
     * @param float $price Price
     * 
     * @return boolean
     */
    public function updateProductQtyInCart($productID, $name, $quantity, $price)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);

        // If qty is missing, assume qty=1
        if (!isset($quantity)) {
            $quantity = 1;
        }
        // If quant is 0, remove item from cart :'[
        if (empty($quantity)) {
            self::removeProductFromCart($productID);
            return true;
        }
        // Add product to cart or update quantity if exists:
        if (!empty($this->data['products'][$productID])) {
            \Errors::debugLogger(__METHOD__ . ':  Updating existing product in cart');
            // If trying to set new qty same as old qty - skip/return true
            if ($this->data['products'][$productID]['qty'] == $quantity) {
                return true;
            }
            // If trying to set new qty
            $this->data['products'][$productID]['qty'] = $quantity;
            return true;
        } else {
            \Errors::debugLogger(__METHOD__ . ':  Adding new product to cart');
            $this->data['products'][$productID]['id']    = $productID;
            $this->data['products'][$productID]['qty']   = $quantity;
            $this->data['products'][$productID]['price'] = $price;
            $this->data['products'][$productID]['name']  = $name;
        }
    }

    /**
     * updateProductOptionInCart
     * 
     * Updates product options in cart
     * 
     * @version v0.0.1
     * 
     * @since v0.0.1
     * 
     * @param int $productID
     * @param int $optionID
     * @param int $choiceID
     * @param float $price
     * @param string $choiceValue Optional textarea/text custom value
     * @param int $choiceImageID Optional image ID to associate with choice (for cart view)
     * 
     * @return boolean
     */
    public function updateProductOptionInCart($productID, $optionID, $choiceID, $price, $choiceValue = NULL, $choiceImageID = NULL)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->data['products'][$productID]['options'][$optionID]['optionID']                       = $optionID;
        $this->data['products'][$productID]['options'][$optionID]['choices'][$choiceID]['choiceID'] = $choiceID;
        if (!empty($choiceValue)) {
            $this->data['products'][$productID]['options'][$optionID]['choices'][$choiceID]['choiceValue'] = $choiceValue;
        } else {
            $this->data['products'][$productID]['options'][$optionID]['choices'][$choiceID]['choiceValue'] = $choiceID;
        }
        if (!empty($choiceImageID)) {
            $this->data['products'][$productID]['options'][$optionID]['choices'][$choiceID]['choiceImageID'] = $choiceImageID;
        }
        $this->data['products'][$productID]['options'][$optionID]['choices'][$choiceID]['price'] = $price;
        return true;
    }

    /**
     * removeProductFromCart
     *
     * Removes product from cart
     * 
     * @version v0.0.1
     * 
     * @param type $productID
     * 
     * @return boolean
     */
    public function removeProductFromCart($productID)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        if (isset($this->data['products'][$productID])) {
            unset($this->data['products'][$productID]);
            return true;
        }
        trigger_error("Error #C000 (delete_product_from_cart): Invalid results.", E_USER_ERROR);
        return false;
    }

    /**
     * createAddressID
     *
     * Create address ID
     * 
     * @param string $firstName
     * @param string $middleName
     * @param string $lastName
     * @param string $phone
     * @param string $email
     * @param string $addressLine1
     * @param string $addressLine2
     * @param string $addressLine3
     * @param string $city
     * @param string $zip
     * @param string $state
     * @param string $country
     * @param string $notes
     * 
     * @return boolean|int 
     */
    public function createAddress($firstName, $middleName, $lastName, $phone, $email, $addressLine1, $addressLine2, $addressLine3,
                                  $city, $zip, $state, $country, $notes)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql     = "
            INSERT INTO addresses
                (firstName, middleName, lastName, phone, email, line1, line2, line3, city, zipPostcode, stateProvinceCounty, country, addressNotes)
            VALUES
                (:firstName, :middleName, :lastName, :phone, :email, :line1, :line2, :line3, :city, :zipPostcode, :stateProvinceCounty, :country, :addressNotes)";
        $this->sqlData = array(':firstName'           => $firstName,
            ':middleName'          => $middleName,
            ':lastName'            => $lastName,
            ':phone'               => $phone,
            ':email'               => $email,
            ':line1'               => $addressLine1,
            ':line2'               => $addressLine2,
            ':line3'               => $addressLine3,
            ':city'                => $city,
            ':zipPostcode'         => $zip,
            ':stateProvinceCounty' => $state,
            ':country'             => $country,
            ':addressNotes'        => $notes);
        $id            = $this->DB->query($this->sql, $this->sqlData);
        if (!isset($id)) {
            trigger_error("Unexpected results", E_USER_ERROR);
            return false;
        }
        return $id;
    }

    /**
     * saveCustomerAddress
     * 
     * Associates address ID to customer ID
     * 
     * @param int $customerID Customer ID
     * @param int $addressID Address ID
     * @param string $addressType Address Type
     * 
     * @return boolean
     */
    public function saveCustomerAddress($customerID, $addressID, $addressType)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql     = "
            INSERT INTO customerAddresses
                (customerID, addressID, addressTypeCode, dateFrom, dateTo)
            VALUES
                (:customerID, :addressID, :addressTypeCode, :dateFrom, :dateTo)";
        $this->sqlData = array(':customerID'      => $customerID,
            ':addressID'       => $addressID,
            ':addressTypeCode' => $addressType,
            ':dateFrom'        => Util::getDateTimeUTC(),
            ':dateTo'          => 'NULL');
        $res           = $this->DB->query($this->sql, $this->sqlData);
        if (!isset($res)) {
            trigger_error("Unexpected results", E_USER_ERROR);
            return false;
        }
        return true;
    }

    /**
     * setBillingAddressID
     * 
     * Saves billing address ID to session
     * 
     * @param int $id Billing Address ID
     */
    public function setBillingAddressID($id)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->data['session']['billing']['addressID'] = $id;
    }

    /**
     * setBillingAVS
     * 
     * Saves billing AVS to session
     * 
     * @param string $address
     * @param string $zip
     */
    public function setBillingAVS($address, $zip)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->data['session']['billing']['AVSAddress'] = $address;
        $this->data['session']['billing']['AVSZip']     = $zip;
    }

    /**
     * setEmailTo
     * 
     * Saves customer email address to session for order receipt
     * 
     * @param string $email
     */
    public function setEmailTo($email)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        return $this->data['session']['emailTo'] = $email;
    }

    /**
     * sanitizePost
     * @param array $args Validation and sanitization rules
     * @return array $san Sanitized post values
     */
    public function sanitizePost($args)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $s   = new Sanitize;
        if (!$san = $s->filterArray(INPUT_POST, $args)) {
            return false;
        }
        return $san;
    }

    /**
     * detectCreditCardType
     * 
     * Detects credit card type using regular expressions against the card number
     * 
     * @param string $number Credit card number (typically only first 6 digits are required)
     * 
     * @return string Type
     */
    public function detectCreditCardType($number)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        if (preg_match('/^3[47]/', $number)) {
            $type = 'AMEX';
        } elseif (preg_match('/^4/', $number)) {
            $type = 'VISA';
        } elseif (preg_match('/^5[1-5]/', $number)) {
            $type = 'MC';
        } elseif (preg_match('/^6(?:011|44|5[0-9]{2})/', $number)) {
            $type = 'DISC';
        } else {
            $type = 'UNKNOWN';
        }
        return $type;
    }

    /**
     * savePaymentMethod
     * 
     * Creates or saves payment method information
     * 
     * @version v0.0.1
     * 
     * @param int $cartID Cart ID
     * @param string $number1 Number1 (Should be encrypted when passed in)
     * @param string $number2 Number2
     * @param string $number3 Number3
     * @param string $expMonth Expiration Month
     * @param string $expYear Expiration year
     * @param string $type Type
     * @param string $notes Notes
     * @param int $paymentMethodID Optional Payment method ID
     * 
     * @return boolean|int Payment Method ID
     */
    public function savePaymentMethod($cartID, $number1, $number2, $number3, $expMonth, $expYear, $notes, $paymentMethodID = FALSE)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        // New or existing
        if (empty($paymentMethodID)) {
            $paymentMethodID = 'DEFAULT';
        }
        // Save Payment Method
        $this->sql     = "
            INSERT INTO paymentMethods
                (paymentMethodID, number1, number2, number3, expMonth, expYear, paymentMethodNotes)
			VALUES
    			(:paymentMethodID, :number1, :number2, :number3, :expMonth, :expYear, :paymentMethodNotes)
            ON DUPLICATE KEY UPDATE
                paymentMethodID = :paymentMethodID,
                number1 = :number1,
                number2 = :number2,
                number3 = :number3,
                expMonth = :expMonth,
                expYear = :expYear,
                paymentMethodNotes = :paymentMethodNotes";
        $this->sqlData = array(':paymentMethodID'    => $paymentMethodID,
            ':number1'            => $number1,
            ':number2'            => $number2,
            ':number3'            => $number3,
            ':expMonth'           => $expMonth,
            ':expYear'            => $expYear,
            ':paymentMethodNotes' => $notes);
        $id            = $this->DB->query($this->sql, $this->sqlData);
        if (!isset($id)) {
            trigger_error('Unexpected results', E_USER_ERROR);
            return false;
        }
        return $id;
    }

    /**
     * createCustomerID
     *
     * Creates new customer ID
     * 
     * @param int $cartID Cart ID
     * 
     * @return boolean|int Customer ID
     */
    public function createCustomerID($cartID)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql                           = "
            INSERT INTO customers
                (cartID)
            VALUES
                (:cartID)";
        $this->sqlData                       = array(':cartID' => $cartID);
        $this->data['session']['customerID'] = $this->DB->query($this->sql, $this->sqlData);
        if (!isset($this->data['session']['customerID'])) {
            trigger_error("Unexpected results", E_USER_ERROR);
            return false;
        }
        return $this->data['session']['customerID'];
    }

    /**
     * saveCustomerInfo
     * 
     * Saves new customer info
     * 
     * @param int $customerID CustomerID
     * @param string $firstName Firstname
     * @param string $middleName Middlename
     * @param string $lastName Lastname
     * @param string $phone Phone
     * @param string $email Email
     * @param string $notes Notes
     * @param string $fingerprint Fingerprint
     * @param string $visitorIP VisitorIP
     * 
     * @return boolean
     */
    public function saveCustomerInfo($customerID, $firstName, $middleName, $lastName, $phone, $email, $notes, $fingerprint, $visitorIP)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql     = "
            INSERT INTO customerInfo
                (customerID, firstName, middleName, lastName, phone, email, notes, fingerprint, visitorIP, regDate)
            VALUES
                (:customerID, :firstName, :middleName, :lastName, :phone, :email, :notes, :fingerprint, :visitorIP, :regDate)";
        $this->sqlData = array(':customerID'  => $customerID,
            ':firstName'   => $firstName,
            ':middleName'  => $middleName,
            ':lastName'    => $lastName,
            ':phone'       => $phone,
            ':email'       => $email,
            ':notes'       => $notes,
            ':fingerprint' => $fingerprint,
            ':visitorIP'   => $visitorIP,
            ':regDate'     => Util::getDateTimeUTC());
        $res           = $this->DB->query($this->sql, $this->sqlData);
        if (!isset($res)) {
            trigger_error('Unexpected results', E_USER_ERROR);
            return false;
        }
        return true;
    }

    /**
     * saveCustomerPaymentMethod
     * 
     * Saves customer ID and payment method ID association
     * 
     * @param int $customerID
     * @param int $paymentMethodID
     * @param string $type
     * @param int $billingAddressID
     * 
     * @return boolean
     */
    public function saveCustomerPaymentMethod($customerID, $paymentMethodID, $type, $billingAddressID)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql     = "
            INSERT INTO customerPaymentMethods
				(customerID, paymentMethodID, paymentMethodCode, billingAddressID, dateFrom, dateTo)
            VALUES
				(:customerID, :paymentMethodID, :paymentMethodCode, :billingAddressID, :dateFrom, :dateTo)";
        $this->sqlData = array(':customerID'        => $customerID,
            ':paymentMethodID'   => $paymentMethodID,
            ':paymentMethodCode' => $type,
            ':billingAddressID'  => $billingAddressID,
            ':dateFrom'          => Util::getDateTimeUTC(),
            ':dateTo'            => 'NULL');
        $res           = $this->DB->query($this->sql, $this->sqlData);
        if (!isset($res)) {
            trigger_error('Unexpected results', E_USER_ERROR);
            return false;
        }
        return true;
    }

    /**
     * saveOrderPaymentMethodID
     * 
     * Saves order ID and payment method ID association
     * 
     * @param type $customerID
     * @param type $orderID
     * @param type $paymentMethodID
     * 
     * @return boolean
     */
    public function saveOrderPaymentMethodID($customerID, $orderID, $paymentMethodID)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql     = "
            UPDATE customerOrders
            SET paymentMethodID = :paymentMethodID
            WHERE customerID = :customerID
            AND orderID = :orderID";
        $this->sqlData = array(':customerID'      => $customerID,
            ':orderID'         => $orderID,
            ':paymentMethodID' => $paymentMethodID);
        $res           = $this->DB->query($this->sql, $this->sqlData);
        if (!isset($res)) {
            trigger_error('Unexpected results', E_USER_ERROR);
            return false;
        }
        return true;
    }

    /**
     * getCartPaymentMethods
     *
     * @return array Payment methods
     */
    public function getCartPaymentMethods()
    {
        \Errors::debugLogger(__METHOD__, 5);
        $this->sql = "
				SELECT
                    `paymentMethodCode`, `paymentMethodDescription`
				FROM
                    `refPaymentMethodTypes`";
        return $this->DB->query($this->sql);
    }

    /**
     * getCartAddressTypes
     *
     * Returns address types
     * 
     * @return array Address types
     */
    public function getCartAddressTypes()
    {
        \Errors::debugLogger(__METHOD__, 5);
        $this->sql = "
				SELECT addressTypeCode, addressTypeDescription
				FROM refAddressTypes
                ORDER BY sortOrder ASC";
        return $this->DB->query($this->sql);
    }

    /**
     * calcCartTotal
     * 
     * Returns the price subtotal of all products/options x quantity in cart
     * 
     * @since v0.0.1
     * 
     * @return float
     */
    public function calcCartTotal()
    {
        \Errors::debugLogger(__METHOD__, 5);
        // Get count of products in cart
        $total_cart_qty      = 0;
        $total_cart_subtotal = 0;
        $product             = new Product($this->session['cartID']);
        foreach ($this->products as $cp) {
            $total_cart_qty += $cp['qty'];
            $total_cart_subtotal += ($cp['price'] * $cp['qty']);
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
                $total_cart_subtotal += $total_cart_options;
            }
        }
        return Util::getFormattedNumber($total_cart_subtotal);
    }

    /**
     * calcCartTaxRate
     * 
     * Returns carts tax rate for selected state
     * 
     * @since v0.0.1
     * 
     * @param int $cartID Cart ID
     * @param string $stateCode State code
     * 
     * @return float
     */
    public function calcCartTaxRate($cartID, $stateCode)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $taxRate = 0;
        $cart   = new KillerCart();
        foreach ($cart->getCartTaxRates($cartID) as $str) {
            if ($str['stateCode'] != $stateCode) {
                continue;
            }
            $taxRate = $str['stateTaxRate'];
            break;
        }
        \Errors::debugLogger(__METHOD__ . ' Carts state tax rate: ' . $taxRate);
        return Util::getFormattedNumber($taxRate);
    }

    /**
     * calcCartTaxable
     * 
     * Returns the taxable amount of the cart
     * 
     * @since v0.0.1
     * 
     * @return float Taxable amount
     */
    public function calcCartTaxable()
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $taxable = 0;
        $product = new Product();
        // Foreach product in order, Sum taxable total
        foreach ($this->products as $p) {
            $productSum       = $p['price'];
            $productIsTaxable = $product->getProductTaxable($p['id']);
            // Product is taxable
            if (!empty($productIsTaxable)) {
                // Sum taxable options if selected
                if (!empty($this->products[$p['id']]['options'])) {
                    foreach ($this->products[$p['id']]['options'] as $option) {
                        if (!empty($option['choices'])) {
                            foreach ($option['choices'] as $choice) {
                                $productSum += $choice['price'];
                            }
                        }
                    }
                }
                $taxable += ($p['qty'] * $productSum);
            }
        }
        // Taxable amount
        \Errors::debugLogger(__METHOD__ . ' Taxable amount: ' . $taxable);
        return Util::getFormattedNumber($taxable);
    }

    /**
     * createOrder
     * 
     * Creates order for checkout process
     * 
     * @version v0.0.1
     * 
     * @param int $customerID Customer ID
     * @param int $shippingAddressID Shipping Address ID
     * @param float $orderAmount Optional Amount of order
     * @param float $taxableAmount Optional Amount of Taxable total
     * @param float $taxAmount Optional amount of tax
     * @param float $deliveryAmount Optional amount of delivery
     * 
     * @var int $this->orderID Order ID
     * 
     * @return boolean|int Order ID
     */
    public function createOrder($customerID, $shippingAddressID = NULL, $orderAmount = NULL, $taxableAmount = NULL, $taxAmount = NULL,
                                $deliveryAmount = NULL)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $dateUTC                             = Util::getDateTimeUTC();
        // Create Order
        $this->sql                           = "
            INSERT INTO customerOrders
                (customerID, orderStatusCode, deliveryStatusCode, shippingAddressID, dateOrderPlaced, totalOrderPrice,
                totalOrderTax, totalOrderDelivery)
            VALUES
                (:customerID, :orderStatusCode, :deliveryStatusCode, :shippingAddressID, :dateOrderPlaced, :totalOrderPrice,
                :totalOrderTax, :totalOrderDelivery)";
        $this->sqlData                       = array(':customerID'         => $customerID,
            ':orderStatusCode'    => 'INC',
            ':deliveryStatusCode' => 'PND',
            ':shippingAddressID'  => $shippingAddressID,
            ':dateOrderPlaced'    => $dateUTC,
            ':totalOrderPrice'    => $orderAmount,
            ':totalOrderTax'      => $taxAmount,
            ':totalOrderDelivery' => $deliveryAmount);
        $this->data['session']['orderID']    = $this->DB->query($this->sql, $this->sqlData);
        $this->data['session']['customerID'] = $customerID;
        if (empty($this->data['session']['orderID'])) {
            trigger_error('Unexpected results', E_USER_ERROR);
            return false;
        }

        // Order Products
        $this->sql     = "
            INSERT INTO customerOrderProducts
                (orderID, productID, quantity, comments)
            VALUES
                (:orderID, :productID, :quantity, :comments)";
        $this->sqlData = array();
        foreach ($this->data['products'] as $p) {
            $this->sqlData[] = array(':orderID'   => $this->data['session']['orderID'],
                ':productID' => $p['id'],
                ':quantity'  => $p['qty'],
                ':comments'  => '');
        }
        $resOP = $this->DB->query($this->sql, $this->sqlData);
        if (!isset($resOP)) {
            trigger_error('Unexpected results', E_USER_ERROR);
            return false;
        }
        // Order Products Options/Choices
        $hasOptions    = false;
        $this->sql     = "
            INSERT INTO customerOrderProductsOptions
                (orderID, productID, productOptionCustomID, productOptionChoiceCustomID, optionValue)
            VALUES
                (:orderID, :productID, :optionID, :optionDetailID, :optionValue)";
        $this->sqlData = array();
        foreach ($this->data['products'] as $p) {
            if (!empty($p['options'])) {
                $hasOptions = true;
                foreach ($p['options'] as $tpo) {
                    $optionID = $tpo['optionID'];
                    foreach ($p['options'][$optionID]['choices'] as $tpoc) {
                        $choiceID        = $tpoc['choiceID'];
                        $choiceValue     = $tpoc['choiceValue'];
                        $choicePrice     = $tpoc['price'];
                        $this->sqlData[] = array(':orderID'        => $this->data['session']['orderID'],
                            ':productID'      => $p['id'],
                            ':optionID'       => $optionID,
                            ':optionDetailID' => $choiceID,
                            ':optionValue'    => $choiceValue);
                    }
                }
            }
        }
        if ($hasOptions) {
            $resOPOC = $this->DB->query($this->sql, $this->sqlData);
            if (!isset($resOPOC)) {
                trigger_error('Unexpected results', E_USER_ERROR);
                return false;
            }
        }

        // Order Products: Delivery
        $this->sql     = "
            INSERT INTO `customerOrderProductsDeliveryHistory`
                (orderID, productID, dateReported, deliveryStatusCode)
            VALUES
                (:orderID, :productID, :dateReported, :deliveryStatusCode)";
        $this->sqlData = array();
        foreach ($this->data['products'] as $p) {
            $this->sqlData[] = array(':orderID'            => $this->data['session']['orderID'],
                ':productID'          => $p['id'],
                ':dateReported'       => $dateUTC,
                ':deliveryStatusCode' => 'PND');
        }
        $resOPD = $this->DB->query($this->sql, $this->sqlData);
        if (!isset($resOPD)) {
            trigger_error('Unexpected results', E_USER_ERROR);
            return false;
        }

        \Errors::debugLogger(__METHOD__ . ' OrderID: ' . $this->data['session']['orderID'], 9);
        return $this->data['session']['orderID'];
    }

    /**
     * recordOrder
     *
     * Records order in database, triggers email notifications
     * 
     * @version v0.0.1
     * 
     * @param int $customerID Customer ID
     * @param int $orderID Order ID
     * @param float $totalOrderPrice Total Order Price
     * @param string $orderNotes Order Notes
     * @param string $orderIP Order IP
     * @param string $statusCode Status Code
     * 
     * @return boolean
     */
    public function recordOrder($customerID, $orderID, $totalOrderPrice, $orderNotes, $orderIP, $orderStatusCode, $deliveryStatusCode)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $dateUTC = Util::getDateTimeUTC();

        // Save Order Status History
        $this->sql     = "
			INSERT INTO customerOrdersStatusHistory
			(orderID, dateReported, orderStatusCode)
			VALUES
			(:orderID, :dateReported, :orderStatusCode)";
        $this->sqlData = array(':orderID'         => $orderID,
            ':dateReported'    => $dateUTC,
            ':orderStatusCode' => $orderStatusCode);
        $res           = $this->DB->query($this->sql, $this->sqlData);
        if (!isset($res)) {
            trigger_error('Unexpected results', E_USER_ERROR);
            return false;
        }

        // Update customerOrders info
        $this->sql     = "
            UPDATE customerOrders
                SET orderStatusCode = :orderStatusCode,
                    dateOrderPlaced = :dateOrderPlaced,
                    totalOrderPrice = :totalOrderPrice,
                    orderNotes = :orderNotes,
                    orderIP = :orderIP
            WHERE customerID = :customerID
            AND orderID = :orderID
            LIMIT 1";
        $this->sqlData = array(':customerID'      => $customerID,
            ':orderStatusCode' => $orderStatusCode,
            ':dateOrderPlaced' => $dateUTC,
            ':totalOrderPrice' => $totalOrderPrice,
            ':orderNotes'      => $orderNotes,
            ':orderIP'         => $orderIP,
            ':orderID'         => $orderID
        );
        $res           = $this->DB->query($this->sql, $this->sqlData);
        if (!isset($res)) {
            trigger_error('Unexpected results', E_USER_ERROR);
            return false;
        }

        // Order Delivery Status
        $this->sql     = "
            INSERT INTO customerOrdersDeliveryHistory
                (orderID, dateReported, deliveryStatusCode)
            VALUES
                (:orderID, :dateReported, :deliveryStatusCode)";
        $this->sqlData = array(':orderID'            => $orderID,
            ':dateReported'       => $dateUTC,
            ':deliveryStatusCode' => $deliveryStatusCode);
        $res           = $this->DB->query($this->sql, $this->sqlData);
        if (!isset($res)) {
            trigger_error('Unexpected results', E_USER_ERROR);
            return false;
        }

        // Status Label
        if ($orderStatusCode == 'ATH') {
            $statusLabel = 'Authorized';
        } elseif ($orderStatusCode == 'PD') {
            $statusLabel = 'Paid';
        } elseif ($orderStatusCode == 'DCL') {
            $statusLabel = 'Declined';
        } elseif ($orderStatusCode == 'PND') {
            $statusLabel = 'Pending';
        }

        //
        // Cart Email
        //

        $s            = new KillerCart();
        $order        = new Order();
        $product      = new Product();
        $image        = new Image();
        $cartID      = $this->data['session']['cartID'];
        // Max Image Dimensions
        $maxImgWidth  = 384;
        $maxImgHeight = 384;
        // Get best fit for this img
        $images       = $image->getImageParentInfo($cartID, 1);
        $img          = $images[0];
        $bestFit      = $image->getBestFitImage($this->data['session']['cartID'], $img['imageID'], $img['imageWidth'],
                                                $img['imageHeight'], $maxImgWidth, $maxImgHeight);

        // Multiple recipients
        // @todo from domain / multi-cart
        $cartEmail = $s->getCartOrdersEmail($this->data['session']['cartID']);
        $from       = $cartEmail;
        $replyto    = $cartEmail;
        $cc         = FALSE;
        $bcc        = FALSE;
        $subject    = 'Order #' . $this->data['session']['orderID'];

        // Placeholder data to fill in
        $cartLogo              = cartPublicUrl . "getfile.php?cartID=" . $this->data['session']['cartID'] . "&imageID=" . $img['imageID'] . "&w=" . $bestFit['bestWidth'] . "&h=" . $bestFit['bestHeight'];
        $orderDateTime          = Util::getServerDateTimeFromUTCDateTime($dateUTC);
        $orderViewLink          = cartPublicUrl . "admin?p=order&a=view_customer_order_single&s=2&oid=" . $orderID;
        $orderViewLinkTitle     = $orderID . ' [Click to View]';
        $orderTotal             = "$" . Util::getFormattedNumber($totalOrderPrice);
        $orderTax               = $order->calcOrderTax($this->data['session']['cartID'], $orderID);
        $orderSubtotal          = $totalOrderPrice - $orderTax;
        $orderCart             = 'Cart #' . $this->data['session']['cartID'];
        $orderCustomerID        = $customerID;
        $orderPaymentStatus     = $statusLabel;
        $orderProductDetailRows = '';
        // Products
        foreach ($this->data['products'] as $p) {
            // Product Options
            $optionMsg    = '';
            $optionsPrice = 0;
            $options      = $order->getOrderProductsOptions($orderID, $p['id']);
            if (!empty($options)) {
                $optionMsg    = '<h6>Options:</h6>';
                $lastOptionID = null;
                foreach ($options as $option) {
                    $optionID       = $option['productOptionCustomID'];
                    $choiceID       = $option['productOptionChoiceCustomID'];
                    $choiceValue    = $option['optionValue'];
                    $choice         = $product->getProductOptionsChoicesCustom($optionID, $choiceID);
                    $choicePrice    = $choice['choicePriceCustom'];
                    $optionsPrice += $choicePrice;
                    $productOptions = $product->getProductOptionsCustom($cartID, $p['id'], $optionID);
                    $optionName     = $productOptions['optionNameCustom'];
                    if (!empty($lastOptionID) && $lastOptionID != $optionID) {
                        $optionMsg .= '</ul><small>' . $optionName . ':</small><ul>';
                    } elseif (empty($lastOptionID)) {
                        $optionMsg .= '<small>' . $optionName . ':</small><ul>';
                    }
                    $optionMsg .= '<li>' . $choiceValue . ' ($' . $choicePrice . ')</li>';
                    $lastOptionID = $optionID;
                }
                $optionMsg .= '</ul>';
            }

            $orderProductDetailRows .= "
                <tr>
                    <td>" . $p['id'] . "</td>
                    <td>" . $p['name'] . $optionMsg . "</td>
                    <td>" . $p['qty'] . "</td>
                    <td>$" . Util::getFormattedNumber(($p['price']
                            + $optionsPrice) * $p['qty']) . "</td>
                </tr>";
        }

        $orderProductDetailRows .= "
            <tr>
                <td colspan='2' style='background-color:#ccc;'>&nbsp;</td>
                <td>Subtotal</td>
                <td><strong>$" . Util::getFormattedNumber($orderSubtotal) . "</strong></td>
            </tr>
            <tr>
                <td colspan='2' style='background-color:#ccc;'>&nbsp;</td>
                <td>Delivery</td>
                <td><img src='" . sitePublicUrl . "img/unknown.png' data-toggle='tooltip' class='tooltip-on' title='Delivery will be added later if it applies'></td>
            </tr>
            <tr>
                <td colspan='2' style='background-color:#ccc;'>&nbsp;</td>
                <td>Tax</td>
                <td><strong>$" . Util::getFormattedNumber($orderTax) . "</strong></td>
            </tr>
            <tr>
                <td colspan='2' style='background-color:#ccc;'>&nbsp;</td>
                <td>Total</td>
                <td><strong>$" . Util::getFormattedNumber($totalOrderPrice) . "</strong></td>
            </tr>";

        $body = $this->prepareOrderCartEmail($cartLogo, $orderDateTime, $orderViewLink, $orderViewLinkTitle, $orderTotal,
                                              $orderCart, $orderCustomerID, $orderPaymentStatus, $orderProductDetailRows);
        Email::sendEmail($cartEmail, $from, $replyto, $cc, $bcc, $subject, $body);

        //
        // Customer Email
        //
        
        // Multiple recipients
        // @todo from domain / multi-cart
        //$this->data['session']['customerID'];
        //$this->data['session']['billing']['addressID'];
        $to      = $this->data['session']['emailTo'];
        $from    = $cartEmail;
        $replyto = $cartEmail;
        $cc      = FALSE;
        $bcc     = FALSE;
        $subject = 'Your Order #' . $this->data['session']['orderID'];

        // Placeholder data to fill in
        $orderDateTime          = Util::getServerDateTimeFromUTCDateTime($dateUTC);
        $orderTotal             = "$" . Util::getFormattedNumber($totalOrderPrice);
        $orderTax               = $order->calcOrderTax($this->data['session']['cartID'], $orderID);
        $orderSubtotal          = $totalOrderPrice - $orderTax;
        $orderCustomerID        = $customerID;
        $orderPaymentStatus     = $statusLabel;
        $orderProductDetailRows = '';

        // Products
        foreach ($this->data['products'] as $p) {
            // Product Options
            $optionMsg    = '';
            $optionsPrice = 0;
            $options      = $order->getOrderProductsOptions($orderID, $p['id']);
            if (!empty($options)) {
                $optionMsg    = '<h6>Options:</h6>';
                $lastOptionID = null;
                foreach ($options as $option) {
                    $optionID       = $option['productOptionCustomID'];
                    $choiceID       = $option['productOptionChoiceCustomID'];
                    $choiceValue    = $option['optionValue'];
                    $choice         = $product->getProductOptionsChoicesCustom($optionID, $choiceID);
                    $choicePrice    = $choice['choicePriceCustom'];
                    $optionsPrice += $choicePrice;
                    $productOptions = $product->getProductOptionsCustom($cartID, $p['id'], $optionID);
                    $optionName     = $productOptions['optionNameCustom'];
                    if (!empty($lastOptionID) && $lastOptionID != $optionID) {
                        $optionMsg .= '</ul><small>' . $optionName . ':</small><ul>';
                    } elseif (empty($lastOptionID)) {
                        $optionMsg .= '<small>' . $optionName . ':</small><ul>';
                    }
                    $optionMsg .= '<li>' . $choiceValue . ' ($' . $choicePrice . ')</li>';
                    $lastOptionID = $optionID;
                }
                $optionMsg .= '</ul>';
            }

            $orderProductDetailRows .= "
                <tr>
                    <td>" . $p['id'] . "</td>
                    <td>" . $p['name'] . $optionMsg . "</td>
                    <td>" . $p['qty'] . "</td>
                    <td>$" . Util::getFormattedNumber(($p['price']
                            + $optionsPrice) * $p['qty']) . "</td>
                </tr>";
        }

        $orderProductDetailRows .= "
            <tr>
                <td colspan='2' style='background-color:#ccc;'>&nbsp;</td>
                <td>Subtotal</td>
                <td><strong>$" . Util::getFormattedNumber($orderSubtotal) . "</strong></td>
            </tr>
            <tr>
                <td colspan='2' style='background-color:#ccc;'>&nbsp;</td>
                <td>Delivery</td>
                <td><img src='" . cartPublicUrl . "img/unknown.png' data-toggle='tooltip' class='tooltip-on' title='Delivery will be added later if it applies'></td>
            </tr>
            <tr>
                <td colspan='2' style='background-color:#ccc;'>&nbsp;</td>
                <td>Tax</td>
                <td><strong>$" . Util::getFormattedNumber($orderTax) . "</strong></td>
            </tr>
            <tr>
                <td colspan='2' style='background-color:#ccc;'>&nbsp;</td>
                <td>Total</td>
                <td><strong>$" . Util::getFormattedNumber($totalOrderPrice) . "</strong></td>
            </tr>";
        $body = $this->prepareOrderCustomerEmail($cartLogo, $orderDateTime, $orderTotal, $orderCustomerID, $orderID,
                                                 $orderPaymentStatus, $orderProductDetailRows);
        echo $body;
        Email::sendEmail($to, $from, $replyto, $cc, $bcc, $subject, $body);
        return true;
    }

    /**
     * prepareOrderCartEmail
     * 
     * Prepares order email alert template to cart
     * 
     * @param string $cartLogo
     * @param string $orderDateTime
     * @param string $orderViewLink
     * @param string $orderViewLinkTitle
     * @param string $orderTotal
     * @param string $orderCart
     * @param string $orderCustomerID
     * @param string $orderPaymentStatus
     * @param string $orderProductDetailRows
     * 
     * @version v0.0.1
     * 
     * @return string $template Email body
     */
    public function prepareOrderCartEmail($cartLogo, $orderDateTime, $orderViewLink, $orderViewLinkTitle, $orderTotal, $orderCart,
                                           $orderCustomerID, $orderPaymentStatus, $orderProductDetailRows)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        // Template variables to replace and the values to replace with
        $tv       = array(
            '@@cartPublicUrl@@'          => cartPublicUrl, // = Cart public URL
            '@@cartLogoSrc@@'           => $cartLogo, // = Cart logo img src
            '@@orderDateTime@@'          => $orderDateTime, // = Order DateTime
            '@@orderViewLink@@'          => $orderViewLink, // = Order view href link
            '@@orderViewLinkTitle@@'     => $orderViewLinkTitle, // = Order view link label (e.g. the order number: 10000)
            '@@orderTotal@@'             => $orderTotal, // = Order total amount
            '@@orderCart@@'             => $orderCart, // = Order cart label
            '@@orderCustomerID@@'        => $orderCustomerID, // = Order customer ID
            '@@orderPaymentStatus@@'     => $orderPaymentStatus, // = Order payment status
            '@@orderProductDetailRows@@' => $orderProductDetailRows // = Order product detail rows
        );
        // Template file location
        $file     = cartPrivateDir . 'templates/' . $this->data['session']['cartTheme'] . '/checkout/order_cart.html';
        // Load template
        $template = file_get_contents($file);
        // Foreach template variable, find and replace
        foreach ($tv as $f => $r) {
            $template = str_replace($f, $r, $template);
        }
        return $template; // Prepared email body
    }

    /**
     * prepareOrderCustomerEmail
     * 
     * Prepares order email alert template to customer
     * 
     * @version v0.0.1
     * 
     * @param string $cartLogo
     * @param string $orderDateTime
     * @param string $orderViewLink
     * @param string $orderViewLinkTitle
     * @param string $orderTotal
     * @param string $orderCart
     * @param string $orderCustomerID
     * @param string $orderPaymentStatus
     * @param string $orderProductDetailRows
     * 
     * @return string $template Email body
     */
    public function prepareOrderCustomerEmail($cartLogo, $orderDateTime, $orderTotal, $orderCustomerID, $orderID, $orderPaymentStatus,
                                              $orderProductDetailRows)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        // Template variables to replace and the values to replace with
        $tv       = array(
            '@@cartPublicUrl@@'          => cartPublicUrl, // = Cart public URL
            '@@cartLogoSrc@@'           => $cartLogo, // = Cart logo img src
            '@@orderDateTime@@'          => $orderDateTime, // = Order DateTime
            '@@orderTotal@@'             => $orderTotal, // = Order total amount
            '@@orderCustomerID@@'        => $orderCustomerID, // = Order customer ID
            '@@orderID@@'                => $orderID, // = Order ID
            '@@orderPaymentStatus@@'     => $orderPaymentStatus, // = Order payment status
            '@@orderProductDetailRows@@' => $orderProductDetailRows // = Order product detail rows
        );
        // Template file location
        $file     = cartPrivateDir . 'templates/' . $this->data['session']['cartTheme'] . '/checkout/order_customer.html';
        // Load template
        $template = file_get_contents($file);
        // Foreach template variable, find and replace
        foreach ($tv as $f => $r) {
            $template = str_replace($f, $r, $template);
        }
        return $template; // Prepared email body
    }

    /**
     * getCartInfo
     * 
     * @since v0.0.1
     * 
     * @param int $cartID Cart ID
     * 
     * @return array Cart details
     */
    public function getCartInfo($cartID)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql     = "
			SELECT cartID, cartName, cartActive, cartNotes, cartTheme, addressID, emailOrders, emailContact, emailErrors,
                termsOfService, storefrontCarousel, storefrontCategories, storefrontDescription
			FROM carts
			WHERE cartID = :cartID
            LIMIT 1";
        $this->sqlData = array(':cartID' => $cartID);
        $return_arr    = $this->DB->query($this->sql, $this->sqlData);
        if (!empty($return_arr)) {
            $this->data['session']['cartName']         = $return_arr[0]['cartName'];
            $this->data['session']['cartActive']       = $return_arr[0]['cartActive'];
            $this->data['session']['cartNotes']        = $return_arr[0]['cartNotes'];
            $this->data['session']['cartTheme']        = $return_arr[0]['cartTheme'];
            $this->data['session']['cartAddressID']    = $return_arr[0]['addressID'];
            //$this->data['session']['cartPhone']        = $return_arr[0]['phone'];
            $this->data['session']['cartEmailOrders']  = $return_arr[0]['emailOrders'];
            $this->data['session']['cartEmailContact'] = $return_arr[0]['emailContact'];
            $this->data['session']['cartEmailErrors']  = $return_arr[0]['emailErrors'];
            // Image
            if (is_dir(cartImagesDir . $cartID)) {
                $dh       = opendir(cartImagesDir . $cartID);
                while (false !== ($filename = readdir($dh))) {
                    $ext = substr($filename, strrpos($filename, '.') + 1);
                    if (in_array($ext, array("jpg", "jpeg", "png", "gif"))) {
                        $this->data['session']['cartImage'] = $filename;
                        break;
                    }
                }
            }
            return $return_arr[0];
        } else {
            trigger_error("Error #S008: Invalid results.", E_USER_ERROR);
            return false;
        }
    }

    /**
     * emptyCart
     *
     * Empties cart data
     * 
     * @return boolean
     */
    public function emptyCart()
    {
        \Errors::debugLogger(__METHOD__, 5);
        unset($this->data['products']);
        return true;
    }

    /*     * *************************************************
     * Deprecated
     * ************************************************ */
    /**
     * getCartTheme
     *
     * Get carts active theme for template loading
     *
     * @return string Theme name
     * 
     * @deprecated since version v0.0.1 (use getCartInfo() instead)
     */
    public function getCartTheme()
    {
        \Errors::debugLogger(__METHOD__, 10);
        $this->sql  = "
			SELECT `cartTheme`
			FROM `carts`
			WHERE `cartID` = :cartID";
        $selectData = array(':cartID' => $this->data['session']['cartID']);
        $results    = $this->DB->query($this->sql, $selectData);
        if (!empty($results)) {
            return $this->data['session']['cartTheme'] = $results[0]['cartTheme'];
        }
        return false;
    }

    /**
     * getCartName
     * 
     * Get carts active name for template
     * 
     * @since v0.0.1
     *
     * @return string Cart name
     * 
     * @deprecated since version v0.0.1 (use getCartInfo() instead)
     */
    public function getCartName()
    {
        \Errors::debugLogger(__METHOD__, 5);
        $this->sql  = "
			SELECT cartName
			FROM carts
			WHERE cartID = :cartID";
        $selectData = array(':cartID' => $this->data['session']['cartID']);
        $results    = $this->DB->query($this->sql, $selectData);
        if (!empty($results)) {
            return $this->data['session']['cartName'] = $results[0]['cartName'];
        }
        return false;
    }

    
    /*************************************************************/
    /*************************************************************/
    
    
public function saveCartTOS($cartID, $tos)
    {
        \Errors::debugLogger(__METHOD__, 5);
        $this->sql     = "
                UPDATE carts SET termsOfService = :termsOfService
                WHERE cartID = :cartID
                LIMIT 1";
        $this->sqlData = array(':cartID' => $cartID,
            ':termsOfService' => $tos);
        $res           = $this->DB->query($this->sql, $this->sqlData);
        if (!isset($res)) {
            \Errors::debugLogger(__METHOD__ . ': Cart TOS failed');
            trigger_error('Unexpected results.', E_USER_ERROR);
            return false;
        }
    }
    
    /**
     * getCartCount
     * 
     * Gets count of carts matching selected type
     * 
     * @param string $type Type of cart: All, Active, Inactive
     * 
     * @return int Count of carts
     */
    public function getCartCount($type = false)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        if (empty($type) || func_num_args($type) > 1 || is_array($type)) {
            trigger_error("Error #S003: Invalid parameters.", E_USER_ERROR);
            return false;
        }
        if (strtolower($type) == "all") {
            $whereSql = "cartID IS NOT NULL";
        } elseif (strtolower($type) == "active") {
            $whereSql = "cartActive='1'";
        } elseif (strtolower($type) == "inactive") {
            $whereSql = "cartActive='0'";
        } else {
            trigger_error("Error #S004: Invalid parameters.", E_USER_ERROR);
            return false;
        }
        $this->sql  = "
			SELECT COUNT(`cartID`) as Total
			FROM `carts`
			WHERE " . $whereSql;
        $return_arr = $this->DB->query($this->sql);
        if (!empty($return_arr)) {
            return $return_arr[0]['Total'];
        } else {
            return false;
        }
    }

    /**
     * getCartIDs
     * 
     * @version v0.0.1
     * 
     * @param string $type Optional type to return: ALL (default), ACTIVE, INACTIVE
     * 
     * @return array Cart IDs
     */
    public function getCartIDs($type = false)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        if (empty($type)
                || strtolower($type) == "all") {
            $whereSql = "cartID IS NOT NULL";
        } elseif (strtolower($type) == "active") {
            $whereSql = "cartActive='1'";
        } elseif (strtolower($type) == "inactive") {
            $whereSql = "cartActive='0'";
        }
        $this->sql  = "
			SELECT cartID
			FROM carts
			WHERE " . $whereSql . "
            ORDER BY cartActive DESC";
        $res = $this->DB->query($this->sql);
        return $res;
    }

    /**
     * getCartAddressInfo
     *
     * @version v0.0.1
     * 
     * @param int $addressID
     * 
     * @return array Address info
     */
    public function getCartAddressInfo($addressID)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql = "
			SELECT addressID, line1, line2, line3, city, zipPostcode, stateProvinceCounty, country, addressNotes
			FROM addresses
			WHERE addressID = :addressID";
        $this->sqlData = array(':addressID' => $addressID);
        $r         = $this->DB->query($this->sql, $this->sqlData);
        if (!empty($r)) {
            return $r[0];
        } else {
            return false;
        }
    }

    /**
     * getCartTaxRates
     * 
     * Gets carts tax rates from database
     * 
     * @param int $cartID Cart ID
     * 
     * @throws trigger_error If database query fails
     * 
     * @return array Cart tax rates
     */
    public function getCartTaxRates($cartID)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql     = "
            SELECT stateCode, stateLabel, stateTaxRate
            FROM cartsTaxRates
            WHERE cartID = :cartID
            AND stateTaxRate > 0"; // Must include > 0
        $this->sqlData = array(':cartID' => $cartID);
        $res           = $this->DB->query($this->sql, $this->sqlData);
        if (!isset($res)) {
            trigger_error('Unexpected results', E_USER_ERROR);
            return false;
        }
        return $res;
    }

    /**
     * setCartTaxRate
     * 
     * Sets carts tax rate in database
     * 
     * @param int $cartID Cart ID
     * @param string $stateCode State code to update
     * @param string $stateLabel State label to update
     * @param float $stateTaxRate State tax rate to update
     * 
     * @throws trigger_error If database query fails
     * 
     * @return boolean
     */
    public function setCartTaxRate($cartID, $stateCode, $stateLabel, $stateTaxRate)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql     = "
            INSERT INTO cartsTaxRates
                (cartID, stateCode, stateLabel, stateTaxRate)
            VALUES
                (:cartID, :stateCode, :stateLabel, :stateTaxRate)
            ON DUPLICATE KEY UPDATE cartID = :cartID, stateCode = :stateCode, stateLabel = :stateLabel, stateTaxRate = :stateTaxRate";
        $this->sqlData = array(':cartID'      => $cartID, ':stateCode'    => $stateCode, ':stateLabel'   => $stateLabel, ':stateTaxRate' => $stateTaxRate);
        $res           = $this->DB->query($this->sql, $this->sqlData);
        if (!isset($res)) {
            trigger_error('Unexpected results', E_USER_ERROR);
            return false;
        }
        return true;
    }

    /**
     * getCartErrorEmail
     * 
     * Gets email address to send error to
     * 
     * @param int $cartID Cart ID
     * 
     * @return string Email address
     */
    public function getCartErrorEmail($cartID)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql     = "
            SELECT emailErrors
            FROM carts
            WHERE cartID = :cartID
            LIMIT 1";
        $this->sqlData = array(':cartID' => $cartID);
        $res           = $this->DB->query($this->sql, $this->sqlData);
        return $res[0]['emailErrors'];
    }

    /**
     * getCartOrdersEmail
     * 
     * Gets email address to send orders to
     * 
     * @param int $cartID Cart ID
     * 
     * @return string Email address
     */
    public function getCartOrdersEmail($cartID)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql     = "
            SELECT emailOrders
            FROM carts
            WHERE cartID = :cartID
            LIMIT 1";
        $this->sqlData = array(':cartID' => $cartID);
        $res           = $this->DB->query($this->sql, $this->sqlData);
        return $res[0]['emailOrders'];
    }

    /**
     * getStorefrontCarousel
     * 
     * @param int $cartID Cart ID
     * 
     * @return array
     */
    public function getStorefrontCarousel($cartID)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql     = "
            SELECT *
            FROM storefrontCarousel
            WHERE cartID = :cartID
            LIMIT 1";
        $this->sqlData = array(':cartID' => $cartID);
        $res           = $this->DB->query($this->sql, $this->sqlData);
        if (!empty($res)) {
          return $res[0];
        }
        return null;
    }
    
    /**
     * 
     * @param type $cartID
     * @param type $slideNum
     * @param type $newImageID
     */
    public function updateCartCarouselSlide($cartID, $slideNum, $newImageID)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql = "
            UPDATE `storefrontCarousel`
            SET `slide" . $slideNum . "ImageID` = :newImageID
            WHERE `cartID` = :cartID";
        $this->sqlData = array(':cartID' => $cartID,
            ':newImageID' => $newImageID);
        $res = $this->DB->query($this->sql, $this->sqlData);
        return $res;
    }
    
    /**
     * setCartCarousel
     * 
     * @param int $cartID Cart ID
     * 
     * @return array
     */
    public function setCartCarousel($cartID, $carouselEnabled, $carouselInterval,
                $slide1ImageID, $slide1Title, $slide1Description, $slide1URL,
                $slide2ImageID, $slide2Title, $slide2Description, $slide2URL,
                $slide3ImageID, $slide3Title, $slide3Description, $slide3URL,
                $slide4ImageID, $slide4Title, $slide4Description, $slide4URL,
                $slide5ImageID, $slide5Title, $slide5Description, $slide5URL, $cartHomeDesc)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        
        if (empty($slide1ImageID)) { $slide1ImageID = NULL; }
        if (empty($slide2ImageID)) { $slide2ImageID = NULL; }
        if (empty($slide3ImageID)) { $slide3ImageID = NULL; }
        if (empty($slide4ImageID)) { $slide4ImageID = NULL; }
        if (empty($slide5ImageID)) { $slide5ImageID = NULL; }
        
        // Update carts table
        $this->sql = "
          UPDATE `carts`
          SET `storefrontCarousel` = :carouselEnabled, `storefrontDescription` = :cartHomeDesc
          WHERE `cartID` = :cartID";
        $this->sqlData = array(':cartID' => $cartID,
            ':carouselEnabled' => $carouselEnabled,
            ':cartHomeDesc' => $cartHomeDesc);
        $resA = $this->DB->query($this->sql, $this->sqlData);
        
        if ($this->getStorefrontCarousel($cartID) == null)
        {
          // First carousel config
          $this->sql = "
            INSERT INTO `storefrontCarousel`
            (`cartID`, `interval`,
            `slide1ImageID`, `slide1Title`, `slide1Description`, `slide1URL`,
            `slide2ImageID`, `slide2Title`, `slide2Description`, `slide2URL`,
            `slide3ImageID`, `slide3Title`, `slide3Description`, `slide3URL`,
            `slide4ImageID`, `slide4Title`, `slide4Description`, `slide4URL`,
            `slide5ImageID`, `slide5Title`, `slide5Description`, `slide5URL`)
            VALUES
            (:cartID, :interval,
              :slide1ImageID, :slide1Title, :slide1Description, :slide1URL,
              :slide2ImageID, :slide2Title, :slide2Description, :slide2URL,
              :slide3ImageID, :slide3Title, :slide3Description, :slide3URL,
              :slide4ImageID, :slide4Title, :slide4Description, :slide4URL,
              :slide5ImageID, :slide5Title, :slide5Description, :slide5URL)";
          $this->sqlData = array(':cartID' => $cartID,
              ':interval' => $carouselInterval,
              ':slide1ImageID' => $slide1ImageID, ':slide1Title' => $slide1Title, ':slide1Description' => $slide1Description, ':slide1URL' => $slide1URL,
              ':slide2ImageID' => $slide2ImageID, ':slide2Title' => $slide2Title, ':slide2Description' => $slide2Description, ':slide2URL' => $slide2URL,
              ':slide3ImageID' => $slide3ImageID, ':slide3Title' => $slide3Title, ':slide3Description' => $slide3Description, ':slide3URL' => $slide3URL,
              ':slide4ImageID' => $slide4ImageID, ':slide4Title' => $slide4Title, ':slide4Description' => $slide4Description, ':slide4URL' => $slide4URL,
              ':slide5ImageID' => $slide5ImageID, ':slide5Title' => $slide5Title, ':slide5Description' => $slide5Description, ':slide5URL' => $slide5URL);
          $res           = $this->DB->query($this->sql, $this->sqlData);
          return $res;
        }
        
        // Update carousel config
        $this->sql = "
          UPDATE `storefrontCarousel`
          SET `interval` = :interval,
          
            `slide1ImageID` = :slide1ImageID,
            `slide1Title` = :slide1Title,
            `slide1Description` = :slide1Description,
            `slide1URL` = :slide1URL,
            
            `slide2ImageID` = :slide2ImageID,
            `slide2Title` = :slide2Title,
            `slide2Description` = :slide2Description,
            `slide2URL` = :slide2URL,
            
            `slide3ImageID` = :slide3ImageID,
            `slide3Title` = :slide3Title,
            `slide3Description` = :slide3Description,
            `slide3URL` = :slide3URL,
            
            `slide4ImageID` = :slide4ImageID,
            `slide4Title` = :slide4Title,
            `slide4Description` = :slide4Description,
            `slide4URL` = :slide4URL,
            
            `slide5ImageID` = :slide5ImageID,
            `slide5Title` = :slide5Title,
            `slide5Description` = :slide5Description,
            `slide5URL` = :slide5URL

          WHERE `cartID` = :cartID";
        $this->sqlData = array(':cartID' => $cartID,
              ':interval' => $carouselInterval,
              ':slide1ImageID' => $slide1ImageID, ':slide1Title' => $slide1Title, ':slide1Description' => $slide1Description, ':slide1URL' => $slide1URL,
              ':slide2ImageID' => $slide2ImageID, ':slide2Title' => $slide2Title, ':slide2Description' => $slide2Description, ':slide2URL' => $slide2URL,
              ':slide3ImageID' => $slide3ImageID, ':slide3Title' => $slide3Title, ':slide3Description' => $slide3Description, ':slide3URL' => $slide3URL,
              ':slide4ImageID' => $slide4ImageID, ':slide4Title' => $slide4Title, ':slide4Description' => $slide4Description, ':slide4URL' => $slide4URL,
              ':slide5ImageID' => $slide5ImageID, ':slide5Title' => $slide5Title, ':slide5Description' => $slide5Description, ':slide5URL' => $slide5URL);
          $res           = $this->DB->query($this->sql, $this->sqlData);
        return $res;
    }    
    
    /**
     * getCartContactEmail
     * 
     * Gets email address to send contact to
     * 
     * @param int $cartID Cart ID
     * 
     * @return string Email address
     */
    public function getCartContactEmail($cartID)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql     = "
            SELECT emailContact
            FROM carts
            WHERE cartID = :cartID
            LIMIT 1";
        $this->sqlData = array(':cartID' => $cartID);
        $res           = $this->DB->query($this->sql, $this->sqlData);
        return $res[0]['emailContact'];
    }

    /**
     * saveCart
     * 
     * Sanitizes user input from cart edit form, saves cart in database
     * 
     * @version v0.0.1
     * 
     * @return boolean
     * 
     * @todo Arguments instead of reading post directly
     * @todo Break up into more methods (e.g. address, image)
     */
    public function saveCart()
    {
        \Errors::debugLogger(__METHOD__, 5);
        
        $err = new \Errors();
        $err->dbLogger('*****Saving Cart***** [SESSION:] '.serialize($_SESSION).' [POST:] '.serialize($_POST));
        
        // Validate & Sanitize _POST
        $args = array(
            'cartID'             => FILTER_VALIDATE_INT,
            'cartName'           => FILTER_SANITIZE_SPECIAL_CHARS,
            'cartStatus'         => FILTER_VALIDATE_INT,
            'cartPaymentGateway' => FILTER_VALIDATE_INT, // @todo
            'cartNotes'          => FILTER_UNSAFE_RAW,
            'cartTheme'          => FILTER_SANITIZE_SPECIAL_CHARS,
            'cartAddressID'      => FILTER_SANITIZE_SPECIAL_CHARS, // FILTER_VALIDATE_INT, // @todo
            'cartLine1'          => FILTER_SANITIZE_SPECIAL_CHARS,
            'cartLine2'          => FILTER_SANITIZE_SPECIAL_CHARS,
            'cartLine3'          => FILTER_SANITIZE_SPECIAL_CHARS,
            'cartCity'           => FILTER_SANITIZE_SPECIAL_CHARS,
            'cartZip'            => FILTER_SANITIZE_SPECIAL_CHARS,
            'cartState'          => FILTER_SANITIZE_SPECIAL_CHARS,
            'cartCountry'        => FILTER_SANITIZE_SPECIAL_CHARS,
            'cartAddressNotes'   => FILTER_UNSAFE_RAW,
            //'cartPhone'          => FILTER_SANITIZE_SPECIAL_CHARS,
            'cartEmailOrders'    => FILTER_VALIDATE_EMAIL,
            'cartEmailContact'   => FILTER_VALIDATE_EMAIL,
            'cartEmailErrors'    => FILTER_VALIDATE_EMAIL
        );
        $inp  = filter_input_array(INPUT_POST, $args);

        // New Cart Defaults
        if (empty($inp['cartID'])) {
            $inp['cartID']             = 'DEFAULT';
            $inp['cartStatus']         = 1;
            $inp['cartPaymentGateway'] = 1; // Offline
            $inp['cartTheme']          = 'default';
            $inp['cartAddressID']      = 'DEFAULT';
            $inp['cartLine1']          = 'Cart Address';
            $inp['cartLine2']          = '';
            $inp['cartLine3']          = '';
            $inp['cartCity']           = 'Cart City';
            $inp['cartZip']            = 'Cart Zip';
            $inp['cartState']          = 'Floirda';
            $inp['cartCountry']        = 'US';
            $inp['cartAddressNotes']   = '';
            //$inp['cartPhone']          = '(123) 456-7890';
            $inp['cartEmailOrders']    = 'orders@cart.com';
            $inp['cartEmailContact']   = 'contact@cart.com';
            $inp['cartEmailErrors']    = 'errors@cart.com';
        }
        // Complete Validation
        if (array_search(false, $inp, true) !== false) {
            $invalid = array_map(function($a) {
                        return $a;
                    }, $inp);
            $msg = '';
            foreach ($invalid as $k => $v) {
                if ($v === false) {
                    $msg .= $k . ' ';
                }
            }
            \Errors::debugLogger(__METHOD__ . ': Validation failed: ' . $msg);
            \Errors::debugLogger($invalid);
            return false;
        }

        // Save Address
        $this->sql = "
			INSERT INTO `addresses`
			(`addressID`, `line1`, `line2`, `line3`, `city`, `zipPostcode`, `stateProvinceCounty`, `country`, `addressNotes`)
			VALUES
			(:addressID, :line1, :line2, :line3, :city, :zipPostcode, :stateProvinceCounty, :country, :addressNotes)
			ON DUPLICATE KEY UPDATE `addressID` = :addressID,
									`line1` = :line1,
									`line2` = :line2,
									`line3` = :line3,
									`city` = :city,
									`zipPostcode` = :zipPostcode,
									`stateProvinceCounty` = :stateProvinceCounty,
									`country` = :country,
									`addressNotes` = :addressNotes";
        $sql_data  = array(':addressID'           => $inp['cartAddressID'],
            ':line1'               => $inp['cartLine1'],
            ':line2'               => $inp['cartLine2'],
            ':line3'               => $inp['cartLine3'],
            ':city'                => $inp['cartCity'],
            ':zipPostcode'         => $inp['cartZip'],
            ':stateProvinceCounty' => $inp['cartState'],
            ':country'             => $inp['cartCountry'],
            ':addressNotes'        => $inp['cartAddressNotes']);
        $results   = $this->DB->query($this->sql, $sql_data);
        if (isset($results) && $results > 0) {
            $addressID = $results;
        } else {
            $addressID = $inp['cartAddressID'];
        }

        // Save Cart
        $this->sql = "
			INSERT INTO `carts`
			(`cartID`, `cartName`, `cartActive`, `cartNotes`, `cartTheme`, `addressID`, `emailOrders`, `emailContact`, `emailErrors`)
			VALUES
			(:cartID, :cartName, :cartActive, :cartNotes, :cartTheme, :addressID, :emailOrders, :emailContact, :emailErrors)
			ON DUPLICATE KEY UPDATE `cartID` = :cartID,
									`cartName` = :cartName,
									`cartActive` = :cartActive,
									`cartNotes` = :cartNotes,
									`cartTheme` = :cartTheme,
									`addressID` = :addressID,
									`emailOrders` = :emailOrders,
									`emailContact` = :emailContact,
									`emailErrors` = :emailErrors";
        $sql_data  = array(':cartID'      => $inp['cartID'],
            ':cartName'    => $inp['cartName'],
            ':cartActive'  => $inp['cartStatus'],
            ':cartNotes'   => $inp['cartNotes'],
            ':cartTheme'   => $inp['cartTheme'],
            ':addressID'    => $addressID,
            ':emailOrders'  => $inp['cartEmailOrders'],
            ':emailContact' => $inp['cartEmailContact'],
            ':emailErrors'  => $inp['cartEmailErrors']);
        $results   = $this->DB->query($this->sql, $sql_data);
        if (isset($results) && $results > 0) {
            $cartID               = $results;
            $this->paymentGatewayID = $inp['cartPaymentGateway'];
        } elseif (isset($results) && $results == 0) {
            $this->paymentGatewayID = $inp['cartPaymentGateway'];
        } else {
            \Errors::debugLogger(__METHOD__ . ': Cart failed');
            \Errors::debugLogger($inp);
            \Errors::debugLogger($results);
            trigger_error('Unexpected results.', E_USER_ERROR);
            return false;
        }

        // If New Cart, associate with Offline Payment Procesing Payment Gateway (#1)
        if ($inp['cartID'] == 'DEFAULT') {
            $this->sql     = "
                INSERT INTO cartsPaymentGateways
                    (cartID, gatewayID, gatewayActive)
                VALUES
                    (:cartID, 1, 1)";
            $this->sqlData = array(':cartID' => $cartID);
            $res           = $this->DB->query($this->sql, $this->sqlData);
            if (!isset($res)) {
                \Errors::debugLogger(__METHOD__ . ': Cart PG failed');
                trigger_error('Unexpected results.', E_USER_ERROR);
                return false;
            }
        }

        return true;
    }
    
}
?>