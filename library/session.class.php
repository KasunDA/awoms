<?php

/**
 * Session class
 *
 * Handles session data in database
 *
 * PHP version 5.4
 * 
 * @author    Brock Hensley <Brock@AWOMS.com>
 * 
 * @version   v00.00.0000
 * 
 * @since     v00.00.0000
 */
class Session
{
    /**
     * Class data
     *
     * @var array $data Array holding any class data used in get/set
     */
    public $data = array();

    /**
     * __construct
     * 
     * Magic method executed on new class
     * 
     * @since v00.00.0000
     * 
     * @version v00.00.0000
     */
    public function __construct()
    {
        Errors::debugLogger(__METHOD__, 100);
        $this->DB = new \Database();
        $this->doIKnowYou();
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
     * @version v01.04.02
     * 
     * @uses resumeSession
     * @uses startSession
     * @return boolean True on existing session, False on unknown
     */
    public function doIKnowYou()
    {
        Errors::debugLogger(__METHOD__, 100);
        if (isset($_SERVER['HTTP_COOKIE']) && !empty($_COOKIE[BRAND_LABEL])) {

            Errors::debugLogger(__METHOD__ . ':  Cookie found: ' . $_COOKIE[BRAND_LABEL] . ' getting session from DB...', 100);
            if (self::getSessionFromDB()) {

                Errors::debugLogger(__METHOD__ . ':  Session found in DB, validating IP...', 100);
                if ($this->validateVisitor()) {

                    Errors::debugLogger(__METHOD__ . ':  [Yes!] Welcome back! Resuming session...', 100);
                    if ($this->startSession()) {
                        return true;
                    }
                }
            }
        } else {
            Errors::debugLogger(__METHOD__ . ':  No cookie found...' . serialize($_COOKIE), 100);
        }

        Errors::debugLogger(__METHOD__ . ':  [No.] Hello! Starting session...', 100, true);
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
    public function startNewSession()
    {
        Errors::debugLogger(__METHOD__, 100);
        self::makeFingerprint();
        self::startSession();
        self::saveSessionToDB();
        return true;
    }

    /**
     * saveSessionToDB
     *
     * Saves session data to database
     *
     * @return boolean
     */
    public static function saveSessionToDB()
    {
        Errors::debugLogger(__METHOD__, 100);

        if (empty($_SESSION)
                || empty($_SESSION['fingerprint']))
        {
            Errors::debugLogger(__METHOD__ . ':  *ERROR* Empty Session!', 1, true);
            trigger_error("Error #S000 (saveSessionToDB): Invalid results.", E_USER_ERROR);
            return false;
        }
        
        $sessionData = $_SESSION;
        $sessionData['sessionSaveTime'] = time();

        $Encryption = new Encryption();
        $enc        = $Encryption->encrypt($sessionData['expiresTime'], $sessionData['fingerprint'], serialize($sessionData));
        $DB   = new \Database();
        $sql  = "INSERT INTO `sessions`
					(`fingerprint`, `brandID`, `cartID`, `setTime`, `expiresTime`, `visitorIP`, `session`)
					VALUES
					(:fingerprint, :brandID, :cartID, :setTime, :expiresTime, :visitorIP, :session)
					ON DUPLICATE KEY UPDATE
						`fingerprint` = :fingerprint,
						`brandID` = :brandID,
                        `cartID` = :cartID,
						`setTime` = :setTime,
						`expiresTime` = :expiresTime,
						`visitorIP` = :visitorIP,
						`session` = :session";
        $sql_data   = array(':fingerprint' => $sessionData['fingerprint'],
            ':brandID'     => $sessionData['brandID'],
            ':cartID'     => $sessionData['cartID'],
            ':setTime'     => $sessionData['setTime'],
            ':expiresTime' => $sessionData['expiresTime'],
            ':visitorIP'   => $sessionData['visitorIP'],
            ':session'     => $enc);
        $results    = $DB->query($sql, $sql_data);
        if (!isset($results)) {
            Errors::debugLogger(__METHOD__ . ':  *ERROR* Query:' . PHP_EOL . '--------------------' . PHP_EOL . $sql . PHP_EOL . '--------------------');
            trigger_error("Error #C000 (saveSessionToDB): Invalid results.", E_USER_ERROR);
            return false;
        }
        return true;
    }

    /**
     * getSessionFromDB
     * 
     * Retrieves session from database, decrypts and stores into $this->data
     * 
     * @return bool
     */
    public function getSessionFromDB()
    {
        Errors::debugLogger(__METHOD__, 100);

        $fingerprint = $_COOKIE[BRAND_LABEL];
        $this->sql   = "
			SELECT brandID, cartID, setTime, expiresTime, visitorIP, session
			FROM sessions
			WHERE brandID = :brandID
              AND fingerprint = :fingerprint";
        $sql_data    = array(':brandID'     => BRAND_ID,
            ':fingerprint' => $fingerprint);
        $results     = $this->DB->query($this->sql, $sql_data);

        if (empty($results)) {
            Errors::debugLogger(__METHOD__ . ':  Cant find session in DB: ' . $fingerprint, 1);
            return false;
        }
        $session = $results[0];
        Errors::debugLogger(__METHOD__ . ':  Session found in DB! setTime: ' . $session['setTime'] . ' expiresTime: ' . $session['expiresTime'] . ' fingerprint: ' . $fingerprint, 100);

        $Encryption = new Encryption();
        $decSession = $Encryption->decrypt($session['expiresTime'], $fingerprint, $session['session']);
        $this->data['session'] = unserialize($decSession);

        // Force current theme (@TODO Global theme vs User theme)
        $this->data['session']['storeTheme'] = BRAND_THEME;
        
        if (!isset($this->data['session']['user_logged_in']))
        {
            $this->data['session']['user_logged_in'] = FALSE;
            // @TODO assign to brands anonymous user group?
        }
        
        Errors::debugLogger(__METHOD__ . ':  Session user_logged_in: ' . $this->data['session']['user_logged_in'], 100);
        return true;
    }

    /**
     * startSession
     *
     * Starts custom php session, regens id if not in ajax mode
     *
     * @return boolean
     */
    public function startSession()
    {
        Errors::debugLogger(__METHOD__, 100);
        
        self::setCookie();
        Errors::debugLogger(__METHOD__ . ':  session name: ' . $this->data['session']['name'] . ' session ID: ' . $this->data['session']['fingerprint'] . '...', 100);
        session_id($this->data['session']['fingerprint']); // Fingerprint matches DB entry
        session_name($this->data['session']['name']); // Sets the session name

        if (!@session_start()) {
            Errors::debugLogger(__METHOD__ . ':  ***** SESSION_START FAIL ***** ' . serialize($this->data['session']));
            trigger_error("Error #C000 (session_start): Invalid results.", E_USER_ERROR);
            return false;
        }

        $_SESSION = $this->data['session'];
        
        return true;
    }

    /**
     * validateVisitor
     *
     * Validates current visitor information against database to ensure session is not being hijacked
     *
     * @version v01.04.02
     * 
     * @uses getVisitorIP
     * @return boolean
     * @todo expand validation to browser fingerprint etc.
     */
    private function validateVisitor()
    {
        Errors::debugLogger(__METHOD__, 100);

        $liveIP = Utility::getVisitorIP();
        Errors::debugLogger(__METHOD__ . ':  Comparing get_visitorIP (' . $liveIP . ') with DB (' . $this->data['session']['visitorIP'] . ')',
                            100);
        if ($liveIP == $this->data['session']['visitorIP']) {
            Errors::debugLogger(__METHOD__ . ':  Visitor IP matches what we stored from last session!', 100);
            return true;
        }

        // Visitor IP changed, need to re-login (if this isn't a login attempt)
        Errors::debugLogger(__METHOD__ . ':  Visitor IP does NOT match what we stored from last session! *** ALERT ***: ' . $this->sql,
                            1, TRUE);
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
        Errors::debugLogger(__METHOD__, 100);
        $this->data['session']['visitorIP']   = Utility::getVisitorIP();
        $this->data['session']['fingerprint'] = hash('sha512',
                                                     $this->data['session']['visitorIP'] + uniqid(mt_rand(1, mt_getrandmax()), true));
        Errors::debugLogger(__METHOD__ . ' fingerprint: ' . $this->data['session']['fingerprint'], 100);
        return $this->data['session']['fingerprint'];
    }

    /**
     * setCookie
     *
     * Configures cookie to be saved or resumed
     * 
     * @version v01.04.02
     *
     * @return boolean
     * 
     * @todo Regenerate fingerprint
     * @todo domain
     */
    private function setCookie()
    {
        Errors::debugLogger(__METHOD__, 100);

        if (empty($this->data['session']['setTime'])) {
            Errors::debugLogger(__METHOD__ . ':  New Expires Time', 100);
            $this->data['session']['setTime']     = time();
            $expires                              = 60 * 60 * 24 * 7; // 1 week
            $this->data['session']['expiresTime'] = $expires;
        } else {
            Errors::debugLogger(__METHOD__ . ':  Existing Expires Time', 100);
            // expires = expires - seconds since setTime
            $now     = time();
            $diff    = $now - $this->data['session']['setTime'];
            $expires = $this->data['session']['expiresTime'] - $diff;
            Errors::debugLogger(__METHOD__ . ':  Updated Expires Time: ' . $expires, 100);
        }

        // Cookie parameters
        Errors::debugLogger('Looking up brand... '.BRAND_ID, 100);
        $Brand = new Brand();
        $brand = $Brand->getSingle(array('brandID' => BRAND_ID));
        
        $this->data['session']['brand'] = $brand;
        $this->data['session']['brandID']    = BRAND_ID;
        $this->data['session']['cartID']    = NULL;
        $this->data['session']['name']       = BRAND_LABEL;

        // If custom Port, exclude Port
        $host = parse_url(BRAND_DOMAIN);
        $host = $host['host'];
        
        // If direct IP, exclude prefix
        if (!preg_match('/^\d/', $host))
        {
            $host = ".".$host;
        }
        
        
        Errors::debugLogger('Domain for cookie: '.$host, 100);
        //$this->data['session']['domain']     = "." . $portTest['host']; // Leading "." allows all sub-domains
        $this->data['session']['domain']     = $host;
        $this->data['session']['storeTheme'] = BRAND_THEME;
        $this->data['session']['https']      = 0;
        
        // Brand/Domain info
        $this->data['session']['domainID'] = BRAND_DOMAIN_ID;
        
        if (HTTPS) {
            Errors::debugLogger('Enabling https...', 100);
            $this->data['session']['https'] = 1;
        }
        $httponly = true; // This stops javascript being able to access the session id.
        $path     = '/';

        // Set custom cookie settings
        Errors::debugLogger(__METHOD__ . ' fingerprint: ' . $this->data['session']['fingerprint'], 100);
        ini_set('session.use_only_cookies', 1); // Forces sessions to only use cookies.
        //$cookieParams = session_get_cookie_params(); // Gets current cookies params.
        session_set_cookie_params($expires, $path, $this->data['session']['domain'], $this->data['session']['https'], $httponly);
    }

    /**
     * removeCookies
     *
     * Removes all cart cookies from visitor browser
     *
     * @version v01.04.02
     * 
     * @return boolean
     */
    public static function removeCookies()
    {
        Errors::debugLogger(__METHOD__, 100);
        if (isset($_SERVER['HTTP_COOKIE'])) {
            $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
            foreach ($cookies as $cookie) {
                $parts = explode('=', $cookie);
                $name  = trim($parts[0]);
                if ($name != BRAND_LABEL) { // $this->data['session']['name'] ?
                    continue;
                }
                setcookie($name, '', time() - 1000);
                setcookie($name, '', time() - 1000, '/');
                Errors::debugLogger(__METHOD__ . ':  Removing cookie ' . $name);
            }
        }
        return true;
    }

}
