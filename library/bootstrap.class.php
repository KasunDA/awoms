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
  public function __construct($url) {
    self::callHook($url);
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
  public function callHook($url) {

    $urlArray = array();
    $urlArray = explode("/", $url);

    if (!empty($urlArray[0])) {      
      $controller = $urlArray[0];
      array_shift($urlArray);
      if (!empty($urlArray[0])) {
        $action = $urlArray[0];
        array_shift($urlArray);
        $queryString = $urlArray;
      } else {
        $action = 'home';
        $queryString = array();
      }
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
      echo 'CCCCCCCCCCC';
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