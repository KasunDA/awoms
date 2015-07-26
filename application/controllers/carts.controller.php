<?php

/**
 * Carts controller
 */
class CartsController extends Controller
{
    /**
     * Log Level
     *
     * @var int $logLevel Config log level used for this class
     */
    protected static $logLevel = 10;

    public function __construct($controller, $model, $action, $template = NULL)
    {
        parent::__construct($controller, $model, $action, $template);
    }

    /**
     * @var static array $staticData
     */
    public static $staticData = array();

}