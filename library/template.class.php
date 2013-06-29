<?php

/**
 * Template class
 *
 * Handles template data
 *
 * PHP version 5.4
 * 
 * @author    dirt <dirt@awoms.com>
 * 
 * @version   v00.00.0000
 * 
 * @since     v00.00.0000
 */
class Template
{

  /**
   * Class data
   *
   * @var array $data Array holding any class data used in get/set
   * @var string $controller Controller
   * @var string $action Action
   */
  protected $data = array(), $controller, $action;

  /**
   * __construct
   * 
   * Magic method executed on new class
   * 
   * @since v00.00.0000
   * 
   * @version v00.00.0000
   * 
   * @param string $controller Controller name
   * @param string $action Action name
   */
  public function __construct($controller, $action) {
    $this->controller = $controller;
    $this->action     = $action;
  }

  public function __set($key, $value) {
    $this->data[$key] = $value;
  }

  public function __get($key) {
    if ($this->__isset($key)) {
      return $this->data[$key];
    }
    return false;
  }

  public function __isset($key) {
    if (array_key_exists($key, $this->data)) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * render
   * 
   * Display template
   * 
   * @since v00.00.0000
   * 
   * @version v00.00.0000
   */
  function render() {

    // Converts all data to variables for template to use
    extract($this->data);

    // Variables
    $viewsFolder = ROOT . DS . 'application' . DS . 'views' . DS;
    $brand = strtolower(BRAND);
    $controller = strtolower($this->controller);

    // Header if not in ajax mode
    if (
      (!isset($_GET['m'])
        || strtolower($_GET['m']) != 'ajax')
      &&
      (!isset($_POST['m'])
        || strtolower($_POST['m']) != 'ajax')
      ) {
      
      if (file_exists($viewsFolder . $brand . DS . $controller . DS . 'header.php')) {
        include ($viewsFolder . $brand . DS . $controller . DS . 'header.php');
      } elseif (file_exists($viewsFolder . $brand . DS . 'header.php')) {
        include ($viewsFolder . $brand . DS . 'header.php');
      } elseif (file_exists($viewsFolder . $controller . DS . 'header.php')) {
        include ($viewsFolder . $controller . DS . 'header.php');
      } else {
        include ($viewsFolder . 'header.php');
      }
          
    }

    // Action
    if (file_exists($viewsFolder . $brand . DS . $controller . DS . $this->action . '.php')) {
      include ($viewsFolder . $brand . DS . $controller . DS . $this->action . '.php');
    } else {
      include ($viewsFolder . $controller . DS . $this->action . '.php');
    }

    // Footer if not in ajax mode
    if (
      (!isset($_GET['m'])
        || strtolower($_GET['m']) != 'ajax')
      &&
      (!isset($_POST['m'])
        || strtolower($_POST['m']) != 'ajax')
      ) {
      
      if (file_exists($viewsFolder . $brand . DS . $controller . DS . 'footer.php')) {
        include ($viewsFolder . $brand . DS . $controller . DS . 'footer.php');
      } elseif (file_exists($viewsFolder . $brand . DS . 'footer.php')) {
        include ($viewsFolder . $brand . DS . 'footer.php');
      } elseif (file_exists($viewsFolder . $controller . DS . 'footer.php')) {
        include ($viewsFolder . $controller . DS . 'footer.php');
      } else {
        include ($viewsFolder . 'footer.php');
      }
      
    }
    
  }

}