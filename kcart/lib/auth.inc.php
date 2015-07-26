<?php
namespace killerCart;

/**
 * Auth class
 *
 * Authentication and Authorization methods
 *
 * PHP version 5
 *
 * @category  killerCart
 * @package   killerCart
 * @author    Brock Hensley <brock@brockhensley.com>
 * @version   v0.0.1
 * @since     v0.0.1
 */
class Auth
{
    /**
     * Database connection
     * Database query sql
     *
     * @var PDO $DB
     * @var string $sql
     */
    protected $DB, $sql, $sqlData;

    /**
     * Class data
     *
     * @var array $data
     */
    protected $data = array();

    /**
     * Main magic methods
     */
    public function __construct()
    {
        \Errors::debugLogger(__METHOD__, 10);
        $this->DB = new \Database(); // new \Database();
    }

    public function __destruct()
    {
        unset($this->DB, $this->sql);
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
     * checkCartUserLogin
     *
     * Authenticate user login details
     *
     * @version v0.0.1
     * 
     * @param string $username
     * @param string $password
     * 
     * @access public
     * 
     * @uses $this->makeKeys() to generate encryption keys on first login
     * 
     * @return boolean
     */
    public function checkCartUserLogin($username, $password)
    {
        \Errors::debugLogger(__METHOD__, 5);
        $this->sql = "
                SELECT
                    su.userID, su.password, su.email, su.userActive,
                    sgu.groupID, sgu.cartID,
                    sug.groupName,
                    c.cartName, c.cartActive, c.cartTheme,
                    sus.lastFailedLoginDate, sus.lastFailedLoginIP, sus.failedLoginAttempts
                FROM cartUsers AS su
                    INNER JOIN cartGroupUsers AS sgu
                        ON su.userID = sgu.userID
                    INNER JOIN userGroups AS sug
                        ON sgu.groupID = sug.usergroupID
                    INNER JOIN carts AS c
                        ON sgu.cartID = c.cartID
                    LEFT JOIN cartUserSettings as sus
                        ON su.userID = sus.userID
                WHERE su.username = :username
                  AND c.cartID = :cartID";
        $sqlData   = array(':username' => $username,
            ':cartID' => CART_ID);
        $u         = $this->DB->query($this->sql, $sqlData);
        
        if (empty($u)) {
            \Errors::debugLogger(__METHOD__ . ': Empty results');
            return false;
        } else {
            $u = $u[0];
        }
        // Verify passphrase
        if (crypt($password, $u['password']) == $u['password']) {
            \Errors::debugLogger(__METHOD__ . ': Passphrase matches');
            // Return false if user or cart is not Active
            if (empty($u['userActive']) || empty($u['cartActive'])) {
                \Errors::debugLogger(__METHOD__ . ': User or cart is Inactive');
                return false;
            }
            // User authenticated, logging history into database
            //                    (userID, regDate, regIP, lastLoginDate, lastLoginIP, failedLoginAttempts)
            $this->sql     = "
                UPDATE cartUserSettings
                SET lastLoginDate = :lastLoginDate,
                    lastLoginIP = :lastLoginIP,
                    failedLoginAttempts = :failedLoginAttempts
                WHERE userID = :userID";
            $this->sqlData = array(':lastLoginDate'       => Util::getDateTimeUTC(),
                ':lastLoginIP'         => Util::getVisitorIP(),
                ':userID'              => $u['userID'],
                ':failedLoginAttempts' => 0);
            $res           = $this->DB->query($this->sql, $this->sqlData);

            // Setting session variables
            \Errors::debugLogger(__METHOD__ . ': Setting admin session variables');
            $_SESSION['cartID']    = $u['cartID'];
            $_SESSION['cartName']  = $u['cartName'];
            $_SESSION['cartTheme'] = $u['cartTheme'];

            $_SESSION['user']['userID']     = $u['userID'];
            $_SESSION['user']['userName']   = $username;
            $_SESSION['user']['usergroup']['usergroupID']    = $u['groupID'];
            $_SESSION['user']['usergroupName']  = $u['groupName'];

            // Save unprotected private key in session for reading of encrypted data throughout session
            $_SESSION['unprotPrivKey'] = $this->getUnprotectedPrivateKey($this->getCartUsersProtectedPrivateKey($_SESSION['user']['userID']),
                                                                                                                 $password);
            // If user has no keypair, call makeKeys to handle
            if (empty($_SESSION['unprotPrivKey'])) {
                \Errors::debugLogger(__METHOD__ . ': Generating initial cart and user encryption keys on first login', 100);
                $this->makeCartUserKeys($_SESSION['cartID'], $_SESSION['user']['userID'], $password);
            }
            return true;
        } else {
            \Errors::debugLogger(__METHOD__ . ':*** Failed login attempt for ' . $username);

            //$fdate = $u['lastFailedLoginDate'];
            //$fip = $u['lastFailedLoginIP'];
            $fattempts     = $u['failedLoginAttempts'];
            // Logging failed attempt in database
            $this->sql     = "
                INSERT INTO cartUserSettings
                    (userID, lastFailedLoginDate, lastFailedLoginIP, failedLoginAttempts)
                VALUES
                    (:userID, :lastFailedLoginDate, :lastFailedLoginIP, :failedLoginAttempts)
                ON DUPLICATE KEY UPDATE userID = :userID, lastFailedLoginDate = :lastFailedLoginDate,
                    lastFailedLoginIP = :lastFailedLoginIP, failedLoginAttempts = :failedLoginAttempts";
            $this->sqlData = array(':userID'              => $u['userID'],
                ':lastFailedLoginDate' => Util::getDateTimeUTC(),
                ':lastFailedLoginIP'   => Util::getVisitorIP(),
                ':failedLoginAttempts' => $fattempts + 1);
            $this->DB->query($this->sql, $this->sqlData);
            return false;
        }
    }

    /**
     * checkCustomerUserLogin
     *
     * Authenticate user login details
     * 
     * @since v0.0.1
     *
     * @param string $username
     * @param string $passphrase
     * 
     * @access public
     * 
     * @uses $this->makeKeys() to generate encryption keys on first login
     * 
     * @return boolean
     */
    public function checkCustomerUserLogin($username, $passphrase)
    {
        \Errors::debugLogger(__METHOD__, 5);
        $this->sql = "
                SELECT
                    ci.customerID, ci.email, ci.username, ci.passphrase, ci.loginAllowed, ci.lastFailedLoginDate, ci.lastFailedLoginIP,
                        ci.failedLoginAttempts,
                    c.cartID, c.customCustomerID,
                    s.cartName, s.cartActive, s.cartTheme
                FROM customerInfo AS ci
                    INNER JOIN customers AS c
                        ON ci.customerID = c.customerID
                    INNER JOIN carts AS s
                        ON c.cartID = s.cartID
                WHERE ci.username = :username
                    AND ci.username <> ''
                ORDER BY ci.customerID DESC
                LIMIT 1";
        $sqlData   = array(':username' => $username);
        $c         = $this->DB->query($this->sql, $sqlData);
        if (empty($c)) {
            \Errors::debugLogger(__METHOD__ . ': Empty results');
            return false;
        } else {
            $c        = $c[0];
            $customer = new Customer();
            $cInfo    = $customer->getCustomerInfo($c['customerID']);
        }
        // Verify passphrase
        if (crypt($passphrase, $c['passphrase']) == $c['passphrase']) {
            \Errors::debugLogger(__METHOD__ . ': Passphrase matches');
            // Return false if user or cart is not Active
            if (empty($c['loginAllowed']) || empty($c['cartActive'])) {
                \Errors::debugLogger(__METHOD__ . ': User or cart is Inactive');
                return false;
            }
            // User authenticated, logging history into database
            //                    (userID, regDate, regIP, lastLoginDate, lastLoginIP, failedLoginAttempts)
            $this->sql     = "
                UPDATE customerInfo
                SET lastLoginDate = :lastLoginDate,
                    lastLoginIP = :lastLoginIP,
                    failedLoginAttempts = :failedLoginAttempts
                WHERE customerID = :customerID";
            $this->sqlData = array(':lastLoginDate'       => Util::getDateTimeUTC(),
                ':lastLoginIP'         => Util::getVisitorIP(),
                ':customerID'          => $c['customerID'],
                ':failedLoginAttempts' => 0);
            $res           = $this->DB->query($this->sql, $this->sqlData);

            // Setting session variables
            \Errors::debugLogger(__METHOD__ . ': Setting customer session variables');
            $_SESSION['cartID']    = $c['cartID'];
            $_SESSION['cartName']  = $c['cartName'];
            $_SESSION['cartTheme'] = $c['cartTheme'];
            $_SESSION['customerID'] = $c['customerID'];

            /*
              $_SESSION['customCustomerID'] = $c['customCustomerID'];
              $_SESSION['user']['userName']         = $c['username'];
              $_SESSION['email']            = $c['email'];
              $_SESSION['firstName']        = $cInfo['firstName'];
              $_SESSION['middleName']       = $cInfo['middleName'];
              $_SESSION['lastName']         = $cInfo['lastName'];
              $_SESSION['phone']            = $cInfo['phone'];
              $_SESSION['regDate']          = $cInfo['regDate'];
             */

            //$_SESSION['fingerprint'] = $c['fingerprint'];
            //$_SESSION['visitorIP'] = $c['visitorIP'];
            // Save unprotected private key in session for reading of encrypted data throughout session
            //$_SESSION['unprotPrivKey'] = $this->getUnprotectedPrivateKey($this->getCartUsersProtectedPrivateKey($_SESSION['customerID']), $passphrase);
            // If user has no keypair, call makeKeys to handle
            //if (empty($_SESSION['unprotPrivKey'])) {
//                \Errors::debugLogger(__METHOD__ . ': Generating initial cart and user encryption keys on first login');
//                $this->makeCartUserKeys($_SESSION['cartID'], $_SESSION['customerID'], $passphrase);
//            }
            return true;
        } else {
            \Errors::debugLogger(__METHOD__ . ':*** Failed login attempt for ' . $username);

            //$fdate = $results[0]['lastFailedLoginDate'];
            //$fip = $results[0]['lastFailedLoginIP'];
            $fattempts     = $c['failedLoginAttempts'];
            // Logging failed attempt in database
            $this->sql     = "
                INSERT INTO customerInfo
                    (customerID, lastFailedLoginDate, lastFailedLoginIP, failedLoginAttempts)
                VALUES
                    (:customerID, :lastFailedLoginDate, :lastFailedLoginIP, :failedLoginAttempts)
                ON DUPLICATE KEY UPDATE customerID = :customerID, lastFailedLoginDate = :lastFailedLoginDate,
                    lastFailedLoginIP = :lastFailedLoginIP, failedLoginAttempts = :failedLoginAttempts";
            $this->sqlData = array(':customerID'          => $c['customerID'],
                ':lastFailedLoginDate' => Util::getDateTimeUTC(),
                ':lastFailedLoginIP'   => Util::getVisitorIP(),
                ':failedLoginAttempts' => $fattempts + 1);
            $this->DB->query($this->sql, $this->sqlData);
            return false;
        }
    }

    /**
     * startSession
     * 
     * Starts session
     * 
     * @version v0.0.1
     * 
     * @param string $name Name of session
     * @param boolean $ajax True = dont regen ID
     * 
     * @todo Increase security (similar to cart sessions) encrypt fingerprint
     * @todo remember_me
     * @todo domain
     */
    public function startSession($name, $ajax = NULL)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 5);
        
        if (!empty($_SESSION))
        {
            return;
        }
        
        $expires = 60 * 60 * 24 * 7; // 1 week
        
        if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) {
            $secure = true;
        } else {
            $secure = false;
        }
        
        $httponly = true; // This stops javascript being able to access the session id.
        ini_set('session.use_only_cookies', 1); // Forces sessions to only use cookies.
        //$cookieParams = session_get_cookie_params(); // Gets current cookies params.
        $path     = '/';
        $domain = ".".$_SERVER['HTTP_HOST'];
        session_set_cookie_params($expires, $path, $domain, $secure, $httponly);
        session_name($name); // Sets the session name
        @session_start(); // Start the php session
        $sid = session_id();
        if (empty($ajax)) {
            session_regenerate_id(true); // regenerated the session, delete the old one.
        }
        
        // Adds customer cookie when admin logging in to allow for customer impersonation
        if ($name == cartCodeNamespace.'Customer') // ... Admin?
        {
            setcookie(cartCodeNamespace.'Customer',
                      session_id(), // $sid
                      time() + (60 * 60 * 24 * 365),
                      '/',
                      ".".$_SERVER['HTTP_HOST'],
                      $secure,
                      $httponly);
        }
    }

    /**
     * updateUserExchange
     * 
     * Allows existing site authentication to impersonate cart customer account bypassing cart re-authentication
     * 
     * @since v0.0.1
     * 
     * @param string $sessionName
     * @param string $sessionValue
     * @param int $authenticated
     * @param string $existingUser
     * @param int $cartCustomerID
     * @param string $sessionExpires
     * @param int $consumed
     * 
     * @return array
     */
    public function updateUserExchange($sessionName, $sessionValue, $authenticated, $existingUser, $cartCustomerID, $sessionExpires,
                                       $consumed = NULL)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql     = "
            INSERT INTO customerUserExchange
                (sessionName, sessionValue, authenticated, existingUser, cartCustomerID, sessionExpires, consumed)
            VALUES
                (:sessionName, :sessionValue, :authenticated, :existingUser, :cartCustomerID, :sessionExpires, :consumed)
            ON DUPLICATE KEY UPDATE
                sessionName = :sessionName, sessionValue = :sessionValue, authenticated = :authenticated, existingUser = :existingUser,
                cartCustomerID = :cartCustomerID, sessionExpires = :sessionExpires, consumed = :consumed";
        $this->sqlData = array(':sessionName'    => $sessionName, ':sessionValue'   => $sessionValue, ':authenticated'  => $authenticated,
            ':existingUser'   => $existingUser, ':cartCustomerID' => $cartCustomerID, ':sessionExpires' => $sessionExpires, ':consumed'       => $consumed);
        return $this->DB->query($this->sql, $this->sqlData);
    }

    /**
     * getUserExchange
     * 
     * Searches for non-consumed user exchange entries
     * 
     * @since v0.0.1
     * 
     * @param string $sessionValue
     * 
     * @return boolean|array
     */
    public function getUserExchange($sessionValue)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql     = "
            SELECT sessionName, sessionValue, authenticated, existingUser, cartCustomerID, sessionExpires, consumed
            FROM customerUserExchange
            WHERE sessionValue = :sessionValue
            AND consumed IS NULL";
        $this->sqlData = array(':sessionValue' => $sessionValue);
        $r             = $this->DB->query($this->sql, $this->sqlData);
        if (empty($r)) {
            return false;
        }
        return $r[0];
    }

    /**
     * makeCartUserKeys
     *
     * Creates and saves in database encryption key for user on first login, also saves priv key in SESSION
     *
     * @version v0.0.1
     * 
     * @param string $passphrase
     * 
     * @return boolean
     */
    public function makeCartUserKeys($cartID, $userID, $passphrase)
    {
        \Errors::debugLogger(__METHOD__, 5);
        // Cart & user new pub/priv keys
        //$opensslConfigPath = NULL;
        /**************/
        $opensslConfigPath = OPENSSL_CONFIG;
        /**************/
        $keyPair                   = self::createKeyPair($opensslConfigPath);
        if (empty($keyPair) || $keyPair === FALSE) { trigger_error('Unable to create key pair', E_USER_ERROR); return false; }
        $protPrivKey               = self::extractProtectedPrivateKey($keyPair, $passphrase, $opensslConfigPath);
        $cartPubKey               = self::extractPublicKey($keyPair);
        
        self::saveCartUserPrivateKey($protPrivKey, $userID);
        self::saveCartPublicKey($cartPubKey, $cartID);
        $unprotPrivKey             = self::getUnprotectedPrivateKey($protPrivKey, $passphrase);
        $_SESSION['unprotPrivKey'] = $unprotPrivKey;
        unset($keyPair, $protPrivKey, $cartPubKey, $unprotPrivKey);
        return true;
    }

    /**
     * changeCartUserPrivateKeyPassphrase
     *
     * Changes cart user passphrase for private key, saves new protected key in database and new unprotected key in session,
     * also saves new user passphrase as they must match
     *
     * @version v0.0.1
     * 
     * @param string $passphrase New passphrase for private key
     * 
     * @todo arguments, userid, cartid
     * 
     * @uses extractProtectedPrivateKey()
     * @uses saveUserPrivateKey()
     * @uses getUnprotectedPrivateKey()
     * @uses User\changeUserPassphrase()
     * 
     * @return boolean True on success
     * 
     */
    public function changeCartUserPrivateKeyPassphrase($passphrase)
    {
        \Errors::debugLogger(__METHOD__, 5);
        
        //$opensslConfigPath = NULL;
//        if (OPMODE == "DEV")
//        {
            $opensslConfigPath = OPENSSL_CONFIG;
//        }
        
        // Change protected private key in database
        $newProtPrivKey = self::extractProtectedPrivateKey($_SESSION['unprotPrivKey'], $passphrase, $opensslConfigPath);
        if (!self::saveCartUserPrivateKey($newProtPrivKey, $_SESSION['user']['userID'])) {
            \Errors::debugLogger(__METHOD__ . ': false saveUserPrivateKey');
            return false;
        }
        // Change unprotected private key in session
        $newUnprotPrivKey          = self::getUnprotectedPrivateKey($newProtPrivKey, $passphrase);
        $_SESSION['unprotPrivKey'] = $newUnprotPrivKey;
        // Change user passphrase in database
        $user                      = new User();
        if (!$user->changeCartUserPassphrase($_SESSION['user']['userID'], $passphrase)) {
            return false;
        }
        return true;
    }

    /**
     * createKeyPair
     *
     * Create private and public key
     *
     * @return resource OpenSSL Key
     * 
     * @todo test for highest available digest_alg
     * @todo test for highest available encrypt_key_cipher
     */
    private function createKeyPair($opensslConfigPath = NULL)
    {
        \Errors::debugLogger(__METHOD__, 5);
        $config = array(
            "digest_alg"         => "sha512",
            "private_key_bits"   => 4096,
            "private_key_type"   => OPENSSL_KEYTYPE_RSA,
            "encrypt_key"        => true,
            "encrypt_key_cipher" => OPENSSL_CIPHER_3DES
        );
        if (!empty($opensslConfigPath))
        {
            $config['config'] = $opensslConfigPath;
        }
        $res = openssl_pkey_new($config);
        return $res;
    }

    /**
     * extractProtectedPrivateKey
     *
     * Extract private key from key pair, protected with $passphrase
     *
     * @param OpenSSL Key $keyPair
     * @param string $passphrase
     * @return string
     */
    public function extractProtectedPrivateKey($keyPair, $passphrase, $opensslConfigPath)
    {
        \Errors::debugLogger(__METHOD__, 5);
        $config = array(
            "digest_alg"         => "sha512",
            "private_key_bits"   => 4096,
            "private_key_type"   => OPENSSL_KEYTYPE_RSA,
            "encrypt_key"        => true,
            "encrypt_key_cipher" => OPENSSL_CIPHER_3DES
        );
        if (!empty($opensslConfigPath))
        {
            $config['config'] = $opensslConfigPath;
        }
        $protPrivKey = NULL;
        $test = openssl_pkey_export($keyPair, $protPrivKey, $passphrase, $config);
        
        if ($test === FALSE || empty($protPrivKey))
        {
            trigger_error("Unable to extract protected private key: $opensslConfigPath", E_USER_ERROR);
        }
        
        return $protPrivKey;
    }

    /**
     * extractPublicKey
     *
     * Extract public key from key pair
     *
     * @param OpenSSL Key $keyPair
     * @return string
     */
    private function extractPublicKey($keyPair)
    {
        \Errors::debugLogger(__METHOD__, 5);
        $pubKey = openssl_pkey_get_details($keyPair);
        return $pubKey["key"];
    }

    /**
     * saveCartUserPrivateKey
     * 
     * Saves cart users protected private key in database
     * 
     * @param string $protPrivKey OpenSSL protected private key
     * @param int $userID User ID
     * 
     * @return boolean True on success
     */
    private function saveCartUserPrivateKey($protPrivKey, $userID)
    {
        \Errors::debugLogger(__METHOD__, 5);
        $this->sql     = "
            UPDATE users
            SET protectedPrivateKey = :protPrivKey
            WHERE userID = :userID
            LIMIT 1";
        $this->sqlData = array(':userID'      => $userID,
            ':protPrivKey' => base64_encode($protPrivKey));
        $res           = $this->DB->query($this->sql, $this->sqlData);
        if (!isset($res)) {
            trigger_error('Unable to save keys!', E_USER_ERROR);
            return false;
        }
        return true;
    }

    /**
     * saveCartPublicKey
     * 
     * Saves carts public key in database
     * 
     * @param string $publicKey OpenSSL public key
     * @param int $cartID Cart ID
     * 
     * @return boolean True on success
     */
    private function saveCartPublicKey($publicKey, $cartID)
    {
        \Errors::debugLogger(__METHOD__, 5);
        $this->sql     = "
            UPDATE carts
            SET cartPublicKey = :publicKey
            WHERE cartID = :cartID
            LIMIT 1";
        $this->sqlData = array(':cartID'   => $cartID,
            ':publicKey' => base64_encode($publicKey));
        $res           = $this->DB->query($this->sql, $this->sqlData);
        if (!isset($res)) {
            trigger_error('Unable to save key!', E_USER_ERROR);
            return false;
        }
        return true;
    }

    /**
     * getCartUsersProtectedPrivateKey
     *
     * Get protected private key from database
     *
     * @param int $userID
     * 
     * @return string $protPrivKey 
     */
    public function getCartUsersProtectedPrivateKey($userID)
    {
        \Errors::debugLogger(__METHOD__, 5);
        $this->sql     = "
            SELECT protectedPrivateKey
            FROM cartUsers
            WHERE userID = :userID
            LIMIT 1";
        $this->sqlData = array(':userID' => $userID);
        $protPrivKey   = $this->DB->query($this->sql, $this->sqlData);
        if (empty($protPrivKey)) {
            \Errors::debugLogger(__METHOD__ . ': empty protPrivKey');
            return false;
        }
        $protPrivKey = base64_decode($protPrivKey[0]['protectedPrivateKey']);
        return $protPrivKey;
    }

    /**
     * getCartUsersPublicKey
     *
     * Gets cart users public key from database
     *
     * @param int $cartID Cart ID
     * 
     * @return string Public key
     */
    public function getCartUsersPublicKey($cartID)
    {
        \Errors::debugLogger(__METHOD__, 5);
        $this->sql     = "
            SELECT cartPublicKey
            FROM carts
            WHERE cartID = :cartID
            LIMIT 1";
        $this->sqlData = array(':cartID' => $cartID);
        $pubKey        = $this->DB->query($this->sql, $this->sqlData);
        if (!isset($pubKey)) {
            \Errors::debugLogger(__METHOD__ . ': No public key found');
            return false;
        }
        return base64_decode($pubKey[0]['cartPublicKey']);
    }

    /**
     * getUnprotectedPrivateKey
     *
     * Get unprotected private key using passphrase
     *
     * @param OpenSSL Key $protPrivKey
     * @param string $passphrase
     * 
     * @return string
     */
    public function getUnprotectedPrivateKey($protPrivKey = NULL, $passphrase = NULL)
    {
        \Errors::debugLogger(__METHOD__, 5);
        if (empty($protPrivKey) || empty($passphrase)) {
            \Errors::debugLogger(__METHOD__ . ': empty');
            return false;
        }
        $unprotPrivKey = openssl_pkey_get_private($protPrivKey, $passphrase);
        
        //$opensslConfigPath = NULL;
//        if (OPMODE == "DEV")
//        {
            $opensslConfigPath = OPENSSL_CONFIG;
        //}
        $config = array(
            "digest_alg"         => "sha512",
            "private_key_bits"   => 4096,
            "private_key_type"   => OPENSSL_KEYTYPE_RSA,
            "encrypt_key"        => true,
            "encrypt_key_cipher" => OPENSSL_CIPHER_3DES
        );
        if (!empty($opensslConfigPath))
        {
            $config['config'] = $opensslConfigPath;
        }
        
        openssl_pkey_export($unprotPrivKey, $unprotPrivKey, NULL, $config);
        return $unprotPrivKey;
    }

    /**
     * encryptData
     *
     * Encrypt data with public key
     *
     * @param OpenSSL PublicKey $pubKey
     * @param string $data
     * 
     * @return string base64 encoded OpenSSL encrypted data
     */
    public function encryptData($pubKey, $data)
    {
        \Errors::debugLogger(__METHOD__, 5);
        $encrypted = false;
        openssl_public_encrypt($data, $encrypted, $pubKey);
        if (empty($encrypted)) {
            trigger_error('Unable to encrypt!', E_USER_ERROR);
            return false;
        }
        return base64_encode($encrypted);
    }

    /**
     * decryptData
     *
     * Decrypt the data using the unprotected private key
     *
     * @param string $encrypted base64 encoded OpenSSL encrypted data
     * @param string $unprotPrivKey OpenSSL PrivateKey
     * 
     * @return string $decrypted Decrypted plaintext
     */
    public function decryptData($encrypted, $unprotPrivKey)
    {
        \Errors::debugLogger(__METHOD__, 5);
        $decrypted = false;
        openssl_private_decrypt(base64_decode($encrypted), $decrypted, $unprotPrivKey);
        if (empty($decrypted)) {
            trigger_error('Unable to decrypt!', E_USER_ERROR);
            return false;
        }
        return $decrypted;
    }

    /**
     * randomPassphrase
     * 
     * Generate random passphrase, simple or complex characters with optional length 
     * 
     * @since v0.0.1
     * 
     * @static
     * 
     * @access public
     * 
     * @param int $len Optional length of password, defaults to 12
     * @param boolean $simple Optional flag for simple characters only (no punctuation etc)
     * 
     * @return string Passphrase
     */
    public static function randomPassphrase($len = NULL, $simple = NULL)
    {
        if ($len === NULL) {
            $len = 12;
        }
        if ($simple !== NULL) {
            return substr(hash('sha512', rand()), 0, $len);
        }
        $r = '';
        for ($i = 0; $i < $len; $i++) {
            $r .= chr(mt_rand(33, 126));
        }
        return $r;
    }

    /*
     * FUTURE USE
     *
      If ($_SESSION['User_LOOSE_IP'] != long2ip(ip2long($_SERVER['REMOTE_ADDR'])
      & ip2long("255.255.0.0"))
      || $_SESSION['User_AGENT'] != $_SERVER['HTTPUser_AGENT']
      || $_SESSION['User_ACCEPT'] != $_SERVER['HTTP_ACCEPT']
      || $_SESSION['User_ACCEPT_ENCODING'] != $_SERVER['HTTP_ACCEPT_ENCODING']
      || $_SESSION['User_ACCEPT_LANG'] != $_SERVER['HTTP_ACCEPT_LANGUAGE']
      || $_SESSION['User_ACCEPT_CHARSET'] != $_SERVER['HTTP_ACCEPT_CHARSET'])
      {
      // Destroy and start a new session
      session_unset(); // Same as $_SESSION = array();
      session_destroy(); // Destroy session on disk
      session_start();
      session_regenerateID(true);

      // Log for attention of admin
      Log::create("Possible session hijacking attempt.", Log::NOTIFY_ADMIN)

      // Flag that the user needs to re-authenticate before continuing.
      Auth::getCurrentUser()->reAuthenticate(Auth::SESSION_SUSPICIOUS);
      }

      // Cart these values into the session so I can check on subsequent requests.
      $_SESSION['User_AGENT']           = $_SERVER['HTTPUser_AGENT'];
      $_SESSION['User_ACCEPT']          = $_SERVER['HTTP_ACCEPT'];
      $_SESSION['User_ACCEPT_ENCODING'] = $_SERVER['HTTP_ACCEPT_ENCODING'];
      $_SESSION['User_ACCEPT_LANG']     = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
      $_SESSION['User_ACCEPT_CHARSET']  = $_SERVER['HTTP_ACCEPT_CHARSET'];

      // Only use the first two blocks of the IP (loose IP check). Use a
      // netmask of 255.255.0.0 to get the first two blocks only.
      $_SESSION['User_LOOSE_IP'] = long2ip(ip2long($_SERVER['REMOTE_ADDR'])
      & ip2long("255.255.0.0"));
     */
}