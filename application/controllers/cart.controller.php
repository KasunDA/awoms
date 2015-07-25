<?php

/**
 * Carts controller
 */
class CartsController extends Controller
{
    /**
     * Log Level
     *
     * @var int $logLevel Config log level; 0 would always be logged, 9999 would only be logged in Dev
     */
    protected static $logLevel = 10;

    public function __construct($controller, $model, $action, $template = NULL)
    {
        parent::__construct($controller, $model, $action, $template);
        echo "<h1>CART__CONSTRUCT</h1>";
    }

    /**
     * @var static array $staticData
     */
    public static $staticData = array();

    public function readall($prepareFormID = FALSE)
    {
        echo "<h1>CART_READALL</h1>";
    }


}