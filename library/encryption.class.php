<?php

/**
 * Encryption class
 *
 * Handles encryption methods
 *
 * PHP version 5.4
 * 
 * @author    Brock Hensley <Brock@brockhensley.com>
 * 
 * @version v0.0.1
 * 
 * @since v0.0.1
 */
class Encryption
{
    /**
     * Class data
     *
     * @var array $data Array holding any class data used in get/set
     */
    protected $data = array();

    /**
     * Log Level
     * 
     * @var int $logLevel Config log level; 0 would always be logged, 9999 would only be logged in Dev 
     */
    protected static $logLevel = 9999;

    /**
     * __construct
     * 
     * Magic method executed on new class
     * 
     * @since v0.0.1
     * 
     * @version v0.0.1
     */
    public function __construct()
    {
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
     * encrypt
     *
     * @param string $key
     * @param string $authKey
     * @param string $plain
     * @return string Encrypted $plain
     */
    function encrypt($key, $authKey, $plain)
    {
        Errors::debugLogger(__METHOD__, Encryption::$logLevel);
        Errors::debugLogger(func_get_args(), Encryption::$logLevel);
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
        Errors::debugLogger(__METHOD__, Encryption::$logLevel);
        Errors::debugLogger(func_get_args(), Encryption::$logLevel);
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
    
}
