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
     * 
     * @todo Session handler?
     */
    public function __construct($url)
    {
        self::lookupDomainBrand();
        self::callHook($url);
    }
    
    public function lookupDomainBrand()
    {
        $Domain    = new Domain();
        $domainIDs = $Domain->getDomainIDs('domainActive=1');
        $domains   = array();

        $foundDomainBrandMatch = FALSE;
        foreach ($domainIDs as $d) {
            $domain    = $Domain->getDomainInfo($d['domainID'], TRUE);
            if ($domain['domainName'] == "") continue; # blank entry protection
            $domains[] = $domain;
            if (!$foundDomainBrandMatch && preg_match("/".$domain['domainName']."/i", $_SERVER['HTTP_HOST']))
            {
                $foundDomainBrandMatch = TRUE;
                if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443)
                {
                    define('HTTPS', TRUE);
                    define('PROTOCOL', 'https://');
                } else {
                    define('HTTPS', FALSE);
                    define('PROTOCOL', 'http://');
                }
                define('BRAND', $domain['brand']['brandName']);
                define('BRANDURL', PROTOCOL.$domain['domainName'].'/');
                define('BRANDLABEL', $domain['brand']['brandLabel']);
                define('BRANDTHEME', $domain['brand']['activeTheme']);
                break; // @todo $api->getSingleByID($id) instead of loading all every time
            }
        }

        if (!$foundDomainBrandMatch) {
            trigger_error("Domain not found that matches ".$_SERVER['HTTP_HOST']);
            die();exit();
        }
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
    public function callHook($url)
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

        $controllerName = $controller;
        $controller     = ucwords($controller);
        $model          = rtrim($controller, 's');
        $controller .= 'Controller';
        
        if (class_exists($controller, TRUE)) {
            $dispatch = new $controller($controllerName, $model, $action);
        }

        // Execute!
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