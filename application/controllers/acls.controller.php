<?php

class ACLsController extends Controller
{

    public function __construct($controller, $model, $action, $template = NULL)
    {
        parent::__construct($controller, $model, $action, $template);
    }

    public function home($args = NULL)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);
        $items = $this->callModelFunc('getWhere', array(NULL, NULL, NULL, NULL, NULL, TRUE));
        $this->set('items', $items);
    }

}