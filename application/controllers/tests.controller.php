<?php

class TestsController extends Controller
{
    public function __construct($controller, $model, $action, $template = NULL)
    {
        parent::__construct($controller, $model, $action, $template);
    }

    public function home($args = NULL)
    {
    }
    
    public function servertest()
    {
    }
}