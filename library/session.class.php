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
    protected $data = array();

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
        Errors::debugLogger(__METHOD__, 10);
        $this->data['session']['brandID'] = BRAND_ID;
        $this->DB                         = new Database();
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
        Errors::debugLogger(__METHOD__, 10);
        if (isset($_SERVER['HTTP_COOKIE'])
                && !empty($_COOKIE[BRAND_LABEL])) {
            Errors::debugLogger(__METHOD__ . ':  Cookie found: ' . $_COOKIE[BRAND_LABEL] . ' validating...', 10);
            if ($this->validateVisitor()) {
                Errors::debugLogger(__METHOD__ . ':  Yes! Welcome back! Resuming session...', 10);
                $this->resumeSession();
                return true;
            }
        } else {
            Errors::debugLogger(__METHOD__ . ':  No cookie found...'.serialize($_COOKIE), 10);
        }
        Errors::debugLogger(__METHOD__ . ':  No. Hello! Starting session...', 10);
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
        Errors::debugLogger(__METHOD__, 10);
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
     */
    public function saveSession()
    {
        Errors::debugLogger(__METHOD__, 10);
        Errors::debugLogger(func_get_args(), 10);
        Errors::debugLogger(__METHOD__.' fingerprint: '.$this->data['session']['fingerprint'], 10);
        
        unset($this->DB);
        $this->DB = null;
        $Encryption = new Encryption();
        $enc = $Encryption->encrypt($this->data['session']['expiresTime'], $this->data['session']['fingerprint'], serialize($this->data));
        $this->DB                         = new Database();
        
        $this->sql = "INSERT INTO `sessions`
					(`fingerprint`, `brandID`, `setTime`, `expiresTime`, `visitorIP`, `session`)
					VALUES
					(:fingerprint, :brandID, :setTime, :expiresTime, :visitorIP, :session)
					ON DUPLICATE KEY UPDATE
						`fingerprint` = :fingerprint,
						`brandID` = :brandID,
						`setTime` = :setTime,
						`expiresTime` = :expiresTime,
						`visitorIP` = :visitorIP,
						`session` = :session";
        $sql_data  = array(':fingerprint' => $this->data['session']['fingerprint'],
            ':brandID'     => $this->data['session']['brandID'],
            ':setTime'     => $this->data['session']['setTime'],
            ':expiresTime' => $this->data['session']['expiresTime'],
            ':visitorIP'   => $this->data['session']['visitorIP'],
            ':session'        => $enc);
        $results   = $this->DB->query($this->sql, $sql_data);
        if (!isset($results)) {
            Errors::debugLogger(__METHOD__ . ':  *ERROR* Query:' . PHP_EOL . '--------------------' . PHP_EOL . $this->sql . PHP_EOL . '--------------------');
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
     */
    public function resumeSession()
    {
        Errors::debugLogger(__METHOD__, 10);
        Errors::debugLogger(func_get_args(), 10);
        $this->sql = "
			SELECT `expiresTime`, `session`
			FROM `sessions`
			WHERE `brandID` = :brandID
				AND `fingerprint` = :fingerprint";
        $sql_data  = array(':brandID'     => $this->data['session']['brandID'],
            ':fingerprint' => $this->data['session']['fingerprint']);
        $results   = $this->DB->query($this->sql, $sql_data);
        if (empty($results)) {
            trigger_error("Error #C000 (resume_session): Invalid results.", E_USER_ERROR);
            return false;
        }
        
        $Encryption = new Encryption();
        $dec = $Encryption->decrypt($results[0]['expiresTime'], $this->data['session']['fingerprint'], $results[0]['session']);
        $this->data                          = unserialize($dec);
        
        // Force current theme
        $this->data['session']['storeTheme'] = BRAND_THEME;
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
        Errors::debugLogger(__METHOD__, 10);
        Errors::debugLogger(func_get_args(), 10);
        $this->sql = "
			SELECT brandID, setTime, expiresTime, visitorIP
			FROM sessions
			WHERE fingerprint = :fingerprint";
        $sql_data  = array(':fingerprint' => $_COOKIE[BRAND_LABEL]);
        $results   = $this->DB->query($this->sql, $sql_data);
        if (!empty($results)) {
            $this->getVisitorIP();
            Errors::debugLogger(__METHOD__ . ':  Comparing get_visitorIP (' . $this->data['session']['visitorIP'] . ') with DB (' . $results[0]['visitorIP'] . ')',
                               10);
            if ($this->data['session']['visitorIP'] == $results[0]['visitorIP']) {
                Errors::debugLogger(__METHOD__ . ':  Visitor IP matches what we stored from last session!', 10);
                // Visitor Validated
                $this->data['session']['fingerprint'] = $_COOKIE[BRAND_LABEL];
                return true;
            } else {
                Errors::debugLogger(__METHOD__ . ':  Visitor IP does NOT match what we stored from last session! *** ALERT ***: ' . $this->sql, 1);
            }
        } else {
            Errors::debugLogger(__METHOD__ . ':  Cant find session: ' . $_COOKIE[BRAND_LABEL], 1);
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
        Errors::debugLogger(__METHOD__, 10);
        $this->getVisitorIP();
        $this->data['session']['fingerprint'] = hash('sha512',
                                                    $this->data['session']['visitorIP'] + uniqid(mt_rand(1, mt_getrandmax()),
                                                                                                                true));
        Errors::debugLogger(__METHOD__.' fingerprint: '.$this->data['session']['fingerprint'], 10);
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
        Errors::debugLogger(__METHOD__, 10);
        $ip = $_SERVER['REMOTE_ADDR'];
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        $this->data['session']['visitorIP'] = $ip;
        return $ip;
    }

    /**
     * setCookie
     *
     * Saves cookie with session identifier on visitor browser
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
        Errors::debugLogger(__METHOD__, 1, true);
        $this->data['session']['name']        = BRAND_LABEL;
        $this->data['session']['setTime']     = time();
        $this->data['session']['expiresTime'] = $this->data['session']['setTime'] + (60 * 60 * 24 * 365);
        $this->data['session']['storeTheme']  = BRAND_THEME;
        $this->data['session']['https'] = 0;
        if (HTTPS) {
            $this->data['session']['https'] = 1;
        }
        $httponly = true; // This stops javascript being able to access the session id.
        $domain = BRAND_DOMAIN;
        Errors::debugLogger(__METHOD__.' fingerprint: '.$this->data['session']['fingerprint'], 1, true);
        if (!setcookie($this->data['session']['name'],
                $this->data['session']['fingerprint'],
                $this->data['session']['expiresTime'],
                '/',
                $domain, $this->data['session']['https'],
                $httponly)
                ) {
            Errors::debugLogger(__METHOD__ . ':  ***** FAIL ***** ' . serialize($this->data['session']));
            trigger_error("Error #C000 (set_cookie): Invalid results.", E_USER_WARNING);
            return false;
        }
        return true;
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
    public function removeCookies()
    {
        Errors::debugLogger(__METHOD__, 10);
        if (isset($_SERVER['HTTP_COOKIE'])) {
            $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
            foreach ($cookies as $cookie) {
                $parts = explode('=', $cookie);
                $name  = trim($parts[0]);
                if ($name != BRAND_LABEL) {
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