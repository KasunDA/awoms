<?php

/**
 * Controller class
 *
 * Handles MVC request
 *
 * PHP version 5.4
 * 
 * @author    Brock Hensley <Brock@AWOMS.com>
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
     * @var int    $step Step 
     */
    protected $controller, $model, $action, $template, $step;

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
     * @param string $template NULL for full page render, "json" for json data w/o header or footers rendered
     */
    public function __construct($controller, $model, $action, $template = NULL)
    {
        self::routeRequest($controller, $model, $action, $template);
        
        // ACL: force login
        if (empty($_SESSION['user_logged_in']) &&
                $controller != "users" &&
                $action != "login")
        {
            #header('Location: /users/login');
        }
        
        // Get step or assume 1st step
        empty($_REQUEST['step']) ? $this->step = 1 : $this->step = $_REQUEST['step'];
        
        $this->set('step', $this->step);
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
    public function __destruct()
    {
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
    public function set($key, $value)
    {
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
    public function routeRequest($controller, $model, $action, $template)
    {
        $this->controller = $controller;
        $this->model      = $model;
        $this->action     = $action;
        $this->$model     = new $model;
        $this->template   = new Template($controller, $action, $template);
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
    public function renderOutput()
    {
        $this->template->render();
    }

    /**
     * Prepares form ID and item ID for create/edit
     * 
     * @param int $ID Existing item ID if edit form otherwise NULL for DEFAULT
     * 
     * @TODO Restrict results by UserID/UsergroupID permissions
     */
    public function prepareForm($ID = NULL, $BrandChoiceList = FALSE, $DomainChoiceList = FALSE, $UsergroupChoiceList = FALSE)
    {
        $action = "Edit";
        if ($ID == NULL) {
            $ID     = "DEFAULT";
            $action = "Create";
        }

        $controllers = ucfirst($this->controller);
        $formID      = "frm" . $action . $controllers;

        $this->set('formID', $formID);
        $controller = rtrim(strtolower($controllers), "s");
        $this->set($controller . 'ID', $ID);

        // Brand selection list
        if ($BrandChoiceList != FALSE) {
            $brands = new BrandsController('brands', 'Brand', NULL, 'json');
            $this->set('brandChoiceList', $brands->GetBrandChoiceList($BrandChoiceList));
        }

        // Domain selection list
        if ($DomainChoiceList != FALSE) {
            $domains = new DomainsController('domains', 'Domain', NULL, 'json');
            $this->set('domainChoiceList', $domains->GetDomainChoiceList($DomainChoiceList));
        }
        
        // Usergroup selection list
        if ($UsergroupChoiceList != FALSE) {
            $usergroups = new UsergroupsController('usergroups', 'Usergroup', NULL, 'json');
            $this->set('usergroupChoiceList', $usergroups->GetUsergroupChoiceList($UsergroupChoiceList));
        }
    }

}