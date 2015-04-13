<?php

/**
 * Bootstrap class
 *
 * Evaluates url request and sends through MVC Controller and renders output
 * through Template
 *
 * PHP version 5.4
 *
 * @author    Brock Hensley <Brock@brockhensley.com>
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
        Errors::debugLogger("*************** BS URL: " . $url . " template(m): " . $template);
        // Install Wizard and Server Tests skip
        if ($url != 'install/wizard' && $url != 'tests/servertest')
        {
            # Look up the requested domain and its matching brand
            self::lookupDomainBrand();

            # Session start/resume
            new Session();
        }
        else
        {
            // Define constants @TODO Move to session?
            if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443)
            {
                define('HTTPS', TRUE);
                define('PROTOCOL', 'https://');
            }
            else
            {
                define('HTTPS', FALSE);
                define('PROTOCOL', 'http://');
            }
            define('BRAND_ID', 0);
            define('BRAND', 'Install Wizard');
            define('BRAND_URL', PROTOCOL . $_SERVER['HTTP_HOST'] . '/');
            define('BRAND_DOMAIN', BRAND);
            define('BRAND_LABEL', BRAND);
            define('BRAND_THEME', BRAND);
        }

        # Process MVC request
        self::callHook($url, $template);
    }

    public function __set($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function __get($key)
    {
        if ($this->__isset($key))
        {
            return $this->data[$key];
        }
        return false;
    }

    public function __isset($key)
    {
        if (array_key_exists($key, $this->data))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * Defines brand constants used throughout application
     *
     * @TODO prevent lookup on every request...
     */
    public static function lookupDomainBrand()
    {
        // Domain lookup
        $Domain = new Domain();
        
        // Remove 'www.' prefix
        $domainName = $_SERVER['HTTP_HOST'];
        $domainName = str_replace("www.", "", $domainName);
        $domain = $Domain->getSingle(array('domainName' => $domainName));
        if (empty($domain))
        {
            $Brand = new Brand();
            $found = $Brand->getWhere();
            if (empty($found))
            {
                // Setup wizard
                header('Location: /install/wizard');
                exit(0);
            }

            trigger_error("Domain not found that matches " . $domainName);
            die();
            exit();
        }

        // Check for Store specific domain/cart
        $cartID = 0;
        if (!empty($domain['storeID']))
        {
            $Store = new Store();
            $store = $Store->getSingle(array('storeID' => $domain['storeID']));
            if (!empty($store['cartID']))
            {
                $cartID = $store['cartID'];
            }
        }

        // Brand lookup
        $Brand = new Brand();
        $brand = $Brand->getSingle(array('brandID' => $domain['brandID']));
        if (empty($brand))
        {
            trigger_error("Brand not found that matches " . $domain['brandID']);
            die();
            exit();
        }
        if ($cartID === 0 && !empty($brand['cartID']))
        {
            $cartID = $brand['cartID'];
        }

        // Define constants @TODO Move to session?
        if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443)
        {
            define('HTTPS', TRUE);
            define('PROTOCOL', 'https://');
        }
        else
        {
            define('HTTPS', FALSE);
            define('PROTOCOL', 'http://');
        }

        define('CART_ID', $cartID);
        define('BRAND_ID', $brand['brandID']);
        define('BRAND', $brand['brandName']);
        define('BRAND_URL', PROTOCOL . $domain['domainName'] . '/');
        define('BRAND_DOMAIN', $domain['domainName']);
        define('BRAND_DOMAIN_ID', $domain['domainID']);
        define('BRAND_LABEL', $brand['brandLabel']);
        define('BRAND_THEME', $brand['activeTheme']);
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
        // Extract Controller/Action/QueryString from URL
        $urlArray   = explode("/", $url);
        $controller = $urlArray[0];
        array_shift($urlArray);
        if (!empty($urlArray[0]))
        {
            $action      = $urlArray[0];
            array_shift($urlArray);
            $queryString = $urlArray;
        }
        else
        {
            $action      = 'home';
            $queryString = array();
        }

        // Save for use throughout scripts
        $_SESSION['controller'] = $controller;
        $_SESSION['action']     = $action;
        $_SESSION['query']      = $queryString;
        $_SESSION['template']   = $template;

        $controllerName = $controller;
        $controller     = ucwords($controller);
        $model          = preg_replace("/s$/", "", $controller); // Brands -> Brand
        $model          = preg_replace("/sss$/", "sses", $model); // Addresses

        $_SESSION['model'] = $model;
        $controller .= 'Controller';

        // Construct Controller
        Errors::debugLogger(__METHOD__.'@'.__LINE__.': Controller: '.$controller);
        if (!class_exists($controller, TRUE))
        {
            Errors::debugLogger(__METHOD__.'@'.__LINE__.': Controller class not found! '.$controller);

            // Check Rewrite Rules (turn /locations/fl into /stores/readall/fl)...
            $Rewrite = new RewriteMapping();
            $globalRules = $Rewrite->getWhere();
            foreach ($globalRules as $globalRule)
            {
                if (!empty($globalRule['domainID'])
                        && $globalRule['domainID'] != BRAND_DOMAIN_ID)
                {
                    continue;
                }

                $check = "/".$controllerName;
                Errors::debugLogger(__METHOD__.'@'.__LINE__.": Checking if $check matches rewriteRule: ".$globalRule['aliasURL']);
                if ($check == $globalRule['aliasURL'])
                {
                    Errors::debugLogger(__METHOD__.'@'.__LINE__.": Constructing new URL from RewriteRule + ReqAction...");
                    $new = $globalRule['actualURL']."/".$action;
                    if (!empty($queryString))
                    {
                        Errors::debugLogger(__METHOD__.'@'.__LINE__.": Appending ReqQueryString...");
                        $new .= "/".$queryString[0];
                    }
                    $new = substr($new, 1); // remove first slash /
                    Errors::debugLogger(__METHOD__." NEW URL: $new");
                    self::callHook($new, $template);
                    return;
                }
            }

        }
        if (class_exists($controller, FALSE))
        {
            Errors::debugLogger(__METHOD__.'@'.__LINE__.': Creating Dispatch...');
            $dispatch = new $controller($controllerName, $model, $action, $template);
        }

        // Execute Action
        if (!method_exists($controller, $action))
        {
            Errors::debugLogger(__METHOD__.'@'.__LINE__.': Method not found ('.$controller.'/'.$action.')! Defaulting to ReadAll...');
            $action = "readall";
            $_SESSION['action'] = $action;
        }
        if (method_exists($controller, $action))
        {
            Errors::debugLogger(__METHOD__.'@'.__LINE__.': Calling Method ('.$controller.'/'.$action.')...');
            call_user_func_array(array($dispatch, $action), $queryString);
        }
        else
        {
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
            if (empty($dispatch))
            {
                trigger_error($errorMsg, E_USER_ERROR);
                return false;
            }
            $dispatch->set('resultsMsg', $errorMsg);

            # @TODO move to error handler
            if (DEVELOPMENT_ENVIRONMENT)
            {
                var_dump($errorMsg);
            } else {
                header('Location: /404.html');
                exit(0);
            }
            
        }
    }

}
