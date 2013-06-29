<?php

/**
 * Autoloader class
 *
 * Loads MVC components as needed
 *
 * PHP version 5.4
 * 
 * @author    dirt <dirt@awoms.com>
 * 
 * @version   v00.00.0000
 * 
 * @since     v00.00.0000
 */
class Autoloader
{

  /**
   * loadClass
   * 
   * @version   v00.00.0000
   * 
   * @since     v00.00.0000
   * 
   * @static Must be static for autoloader
   * 
   * @throws Exception
   * 
   * @param string $class Class name
   * 
   * @todo clean class formattingetc
   */
  public static function loadClass($class) {

    // Controllers need name split
    if (preg_match('/(.+)(Controller)$/', $class, $matches)) {
      $libraries = array(ROOT . DS . 'application' . DS . 'controllers' . DS . strtolower($matches[1]) . '.controller.php');
    } else {
      $libraries = array(
        ROOT . DS . 'library' . DS . strtolower($class) . '.class.php',
        ROOT . DS . 'application' . DS . 'models' . DS . strtolower($class) . '.model.php'
      );
    }
    $loaded = FALSE;
    foreach ($libraries as $library) {
      if (is_file($library)) {
        if (require_once($library)) {
          // echo '<h5>Loaded class '.$class.' @ '.$library.'</h5>';
          $loaded = TRUE;
          break;
        }
      }
    }
    if ($loaded === FALSE) {
      // ERROR
      ob_start();
      var_dump($libraries);
      $result = ob_get_clean();
      trigger_error("
        <h1>Error!</h1>
        <h2>Could not load class</h2>
        <div class='error'>
          File: " . __FILE__ . "<br />
          Line: " . __LINE__ . "<br />
          Class: " . $class . "<br />
          Details: " . $result . "
        </div>", E_USER_ERROR);
      
    }
  }

}