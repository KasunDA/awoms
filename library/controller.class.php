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
        // Perform ACL check prior to routing request
        // action is empty if using controller within code loop (getDomains == new Brand() <-- empty action...)
        if (!empty($controller) && !empty($action)) {
            ACL::IsUserAuthorized($controller, $action, "login");
        }

        self::routeRequest($controller, $model, $action, $template);

        // Get step or assume 1st step (@TODO should this be above reouteRequest?)
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
     * Prepares form ID and item ID for create/update
     * 
     * @param int $ID Existing item ID if update form otherwise NULL for DEFAULT
     * 
     * @TODO Restrict results by UserID/UsergroupID permissions
     */
    public function prepareForm($ID = NULL, $BrandChoiceList = FALSE, $DomainChoiceList = FALSE, $UsergroupChoiceList = FALSE)
    {
        $action = "Update";
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
        if ($BrandChoiceList != FALSE
                || in_array($this->controller, array('domains', 'usergroups', 'menus', 'pages', 'articles'))) {
            $brands = new BrandsController('brands', 'Brand', NULL, 'json');
            $this->set('brandChoiceList', $brands->GetBrandChoiceList($BrandChoiceList));
        }

        // Domain selection list
        if ($DomainChoiceList != FALSE
                || in_array($this->controller, array('pages', 'articles'))) {
            $domains = new DomainsController('domains', 'Domain', NULL, 'json');
            $this->set('domainChoiceList', $domains->GetDomainChoiceList($DomainChoiceList));
        }

        // Usergroup selection list
        if ($UsergroupChoiceList != FALSE
                || in_array($this->controller, array('users'))) {
            $usergroups = new UsergroupsController('usergroups', 'Usergroup', NULL, 'json');
            $this->set('usergroupChoiceList', $usergroups->GetUsergroupChoiceList($UsergroupChoiceList));
        }
    }

    public function create($args = NULL)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);
        
        if ($this->step == 1)
        {
            
        }
        elseif ($this->step == 2)
        {
            // Data array to be passed to sql
            $_model = $this->model;
            $_modelLower = strtolower($this->model);
            $data                = array();
            
            //@TODO Assuming active = 1 on create for now....
            $data[$_modelLower.'Active'] = 1;

            // Gets input data from post, must begin with "inp_"
            foreach ($_POST as $k => $v) {
                if (!preg_match('/^inp_(.*)/', $k, $m)) {
                    continue;
                }

                // Sets template data for re-use in forms
                $this->set($k, $v);

                // Item id (new or existing)
                if ($k == 'inp_'.$_modelLower.'ID') {
                    $inp_itemID = $v;
                }

                // Item info col/data
                $data[$m[1]] = $v;
            }
            
            // Save item info, getting ID
            $itemID = $this->$_model->update($data);
            if ($inp_itemID != 'DEFAULT') {
                // Updated (existing ID)
                $itemID = $inp_itemID;
            }

            $this->set($_modelLower.'ID', $itemID);
            $this->set('success', TRUE);
        }
        
        // Get updated list
        self::readall();
        
        return true;
    }
    
    public function read($args)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);
        return self::itemExists($args);
    }
    
    public function readall($args = NULL)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);
        
        $_modelLower = strtolower($this->model);
        $_model = $this->model;
        $_models = $_model."s";
        $_modelsLower = $_modelLower."s";
        
        // Restrict viewing list to this brand if non-admin
        $_limit = NULL;
        if ($_SESSION['user']['usergroup']['usergroupName'] != "Administrators")
        {
            $_limit = $_modelLower."Active=1 AND brandID = ".$_SESSION['brandID'];
        }
        // $_limit
        
        // Get item list
        $callAction = "getAll";
        
        // Call Model->Action
        $d = call_user_func(array($this->$_model, $callAction));
        $this->set($_modelsLower, $d);
        
        // Prepare Create Form
        self::prepareForm();

        // Prepare UI Template Data
        self::setTitle();
        
        return true;
    }

    public function update($args)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);
        
        return self::itemExists($args);
        
//        } elseif ($this->step == 2) {
//            // Use create method to update existing
//            $res = $this->create();
//        }
        
    }

    public function delete($args)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);
        return self::itemExists($args);
    }

    private function checkACL()
    {
        return true;
    }
    
    private function setTitle()
    {
        $titleController = ucwords($this->controller)."s";
        switch ($this->action)
        {
            case "create":
                $titleAction = "Create";
                break;
            case "read":
                $titleAction = "Read";
                break;
            case "readall":
                $titleAction = "Read All";
                break;
            case "update":
                $titleAction = "Update";
                break;
            case "delete":
                $titleAction = "Delete";
                break;
        }
        $this->set('title', $titleController.' :: '.$titleAction);
    }
    
    /**
     * Ensure item exists otherwise return to readall list
     * 
     * @param array|string $args
     */
    private function itemExists($args)
    {
        $pass = FALSE;
        if (!empty($args)) {
            if (is_array($args)) {
                $ID = $args[0];
            } else {
                $ID = $args;
            }
            $controller = rtrim($this->controller, "s");
            $model      = $this->model;
            $callAction = "get" . $model . "Info";
            $Info       = call_user_func_array(array($this->$model, $callAction), array($ID, TRUE));
            if (!empty($Info)) {
                $pass = TRUE;
                $this->set($controller . 'ID', $ID);
                $this->set($controller, $Info);
            }
        }
        if (!$pass) {
            header('Location: /' . $this->controller . '/readall');
            exit(0);
        }
    }
}