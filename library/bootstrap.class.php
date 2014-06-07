<?php

/**
 * Bootstrap class
 *
 * Evaluates url request and sends through MVC Controller and renders output
 * through Template
 *
 * PHP version 5.4
 * 
 * @author    Brock Hensley <Brock@AWOMS.com>
 * 
 * @version   v00.00.0000
 * 
 * @since     v00.00.0000
 */
class Bootstrap
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
     * 
     * @uses callHook()
     * 
     * @param string $url Url
     */
    public function __construct($url, $template)
    {
        Errors::debugLogger("*************** BS URL: " . $url . " template(m): ".$template);
        
        # Look up the requested domain and its matching brand
        self::lookupDomainBrand();

        # Session start/resume
        new Session();
        
        # Process MVC request
        self::callHook($url, $template);
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
     * Defines brand constants used throughout application
     */
    public function lookupDomainBrand()
    {
        $Domain = new Domain();

        // @TODO Sanitize $_SERVER['HTTP_HOST']
        $lookupDomain = $_SERVER['HTTP_HOST'];
        $where        = "domainActive=1 AND domainName='" . $lookupDomain . "'";
        $d            = $Domain->getDomainIDs($where);

        if (count($d) == 0) {
            trigger_error("Domain not found that matches " . $lookupDomain);
            die();
            exit();
        }

        $domain = $Domain->getDomainInfo($d[0]['domainID'], TRUE);
        if ($domain['domainName'] == "") continue;# blank entry protection

        if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) {
            define('HTTPS', TRUE);
            define('PROTOCOL', 'https://');
        } else {
            define('HTTPS', FALSE);
            define('PROTOCOL', 'http://');
        }
        define('BRAND_ID', $domain['brand']['brandID']);
        define('BRAND', $domain['brand']['brandName']);
        define('BRAND_URL', PROTOCOL . $domain['domainName'] . '/');
        define('BRAND_DOMAIN', $domain['domainName']);
        define('BRAND_LABEL', $domain['brand']['brandLabel']);
        define('BRAND_THEME', $domain['brand']['activeTheme']);
    }

    /**
     * callHook
     * 
     * Constructs controller from request url
     * 
     * @since v00.00.0000
     * 
     * @version v00.00.0000
     * 
     * @uses callHook()
     * 
     * @throws Exception
     * 
     * @param string $url Url
     */
    public function callHook($url, $template)
    {
        $urlArray   = explode("/", $url);
        $controller = $urlArray[0];
        array_shift($urlArray);
        if (!empty($urlArray[0])) {
            $action      = $urlArray[0];
            array_shift($urlArray);
            $queryString = $urlArray;
        } else {
            $action      = 'home';
            $queryString = array();
        }

        // Save for use throughout scripts
        $_SESSION['controller'] = $controller;
        $_SESSION['action'] = $action;
        $_SESSION['query'] = $queryString;
        $_SESSION['template'] = $template;
                
        $controllerName = $controller;
        $controller     = ucwords($controller);
        $model          = rtrim($controller, 's');
        $controller .= 'Controller';

        // Construct
        if (class_exists($controller, TRUE)) {
            $dispatch = new $controller($controllerName, $model, $action, $template);
        }

        // Execute Action
        if (method_exists($controller, $action)) {
            call_user_func_array(array($dispatch, $action), $queryString);
        } else {
            // Does Not Exist
            $errorMsg = "
                <h1>Error!</h1>
                <h2>Method does not exist</h2>
                <div class='error'>
                  File: " . __FILE__ . "<br />
                  Line: " . __LINE__ . "<br />
                  Details: " . "<br />
                  <br />
                  Controller: " . $controller . "<br />
                  Action: " . $action . "
                </div>";
            if (empty($dispatch)) {
                trigger_error($errorMsg, E_USER_ERROR);
                return false;
            }
            $dispatch->set('resultsMsg', $errorMsg);
        }
    }
}