<?php

/**
 * Controller class
 *
 * Handles MVC request
 *
 * PHP version 5.4
 * 
 * @author    dirt <dirt@awoms.com>
 * 
 * @version   v00.00.0000
 * 
 * @since     v00.00.0000
 */
class Controller
{

  /**
   * Class data
   *
   * @var string $controller Controller name
   * @var string $model Model name
   * @var string $action Action name
   * @var string $template Template name
   */
  protected $controller, $model, $action, $template;

  /**
   * __construct
   * 
   * Magic method executed on new class
   * 
   * @since v00.00.0000
   * 
   * @version v00.00.0000
   * 
   * @uses Database()
   * @uses routeRequest()
   * 
   * @param string $controller Controller name
   * @param string $model Model name
   * @param string $action Action name
   */
  public function __construct($controller, $model, $action) {
    self::routeRequest($controller, $model, $action);
  }

  /**
   * __destruct
   * 
   * Magic method executed on class end
   * 
   * @since v00.00.0000
   * 
   * @version v00.00.0000
   * 
   * @uses renderOutput()
   */
  public function __destruct() {
    self::renderOutput();
  }
  
  /**
   * set
   * 
   * Sets template variable
   * 
   * @since v00.00.0000
   * 
   * @version v00.00.0000
   * 
   * @param type $key
   * @param type $value
   */
  public function set($key, $value) {
    $this->template->$key = $value;
  }

  /**
   * routeRequest
   * 
   * Routes request through MVC
   * 
   * @since v00.00.0000
   * 
   * @version v00.00.0000
   * 
   * @uses $model()
   * @uses Template()
   * 
   * @param string $controller Controller name
   * @param string $model Model name
   * @param string $action Action name
   */
  public function routeRequest($controller, $model, $action) {
    $this->controller = $controller;
    $this->model      = $model;
    $this->action     = $action;
    $this->$model     = new $model;
    $this->template   = new Template($controller, $action);
  }

  /**
   * renderOutput
   * 
   * Outputs results through template
   * 
   * @since v00.00.0000
   * 
   * @version v00.00.0000
   */
  public function renderOutput() {
    $this->template->render();
  }

}