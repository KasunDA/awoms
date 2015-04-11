<?php

/**
 * Autoloader class
 *
 * Loads MVC components as needed
 *
 * PHP version 5.4
 * 
 * @author    Brock Hensley <Brock@brockhensley.com>
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
        ROOT . DS . 'application' . DS . 'models' . DS . strtolower($class) . '.model.php');
        // CART: Classname passed must start with namespace e.g. killerCart\Class
        $className = explode('\\', $class);
        if (count($className) > 1 && $className[0] == "killerCart") {
            $className = $className[1];
            $filePath = ROOT . DS . 'cart' . DS . 'lib' . DS . strtolower($className) . '.inc.php';
            $libraries[] = $filePath;
        }
    }
    $loaded = FALSE;
    foreach ($libraries as $library) {
      if (is_file($library)) {
        if (require_once($library)) {
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
      
      #@TODO move to error handler      
      header('Location: /404.html');
      exit(0);
      
    }
  }

}
