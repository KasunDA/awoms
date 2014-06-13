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
     * @param false|ALL|ID $BrandChoiceList pass in ID to pre-select that ID
     * @param false|ALL|ID $DomainChoiceList pass in ID to pre-select that ID
     * @param false|ALL|ID $UsergroupChoiceList pass in ID to pre-select that ID
     */
    public function prepareForm($ID = NULL, $BrandChoiceList = FALSE, $DomainChoiceList = FALSE, $UsergroupChoiceList = FALSE)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);

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
        if ($BrandChoiceList != FALSE || in_array($this->controller, array('domains', 'usergroups', 'menus', 'pages', 'articles'))) {
            $brands = new BrandsController('brands', 'Brand', NULL, 'json');
            $this->set('brandChoiceList', $brands->GetBrandChoiceList($BrandChoiceList));
        }

        // Domain selection list
        if ($DomainChoiceList != FALSE || in_array($this->controller, array('pages', 'articles'))) {
            $domains = new DomainsController('domains', 'Domain', NULL, 'json');
            $this->set('domainChoiceList', $domains->GetDomainChoiceList($DomainChoiceList));
        }

        // Usergroup selection list
        if ($UsergroupChoiceList != FALSE || in_array($this->controller, array('users'))) {
            $usergroups = new UsergroupsController('usergroups', 'Usergroup', NULL, 'json');
            $this->set('usergroupChoiceList', $usergroups->GetUsergroupChoiceList($UsergroupChoiceList));
        }
    }

    /**
     * Home
     * 
     * @param array $args
     * @return boolean
     */
    public function home($args = NULL)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);
        return true;
    }

    /**
     * Create
     * 
     * @param array $args
     * @return boolean
     */
    public function create($args = NULL)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);

        if ($this->step == 1) {
            
        } elseif ($this->step == 2) {
            // Data array to be passed to sql
            $_model      = $this->model;
            $_modelLower = strtolower($this->model);
            $data        = array();

            /* Controller specific: */
            $c = $this->model . "sController";

            // Gets input data from post, must begin with "inp_"
            foreach ($_POST as $k => $v) {
                if (!preg_match('/^inp_(.*)/', $k, $m)) {
                    continue;
                }

                /* Controller specific Step 1: */
                if ($c::createStepInput($k, $v)) continue;

                // Sets template data for re-use in forms
                $this->set($k, $v);

                // Item id (new or existing)
                if ($k == 'inp_' . $_modelLower . 'ID') {
                    $inp_itemID = $v;
                }

                // Item info col/data
                $data[$m[1]] = $v;
            }

            /* Controller specific Step pre-save: */
            $_r = $c::createStepPreSave();
            if (!empty($_r)) {
                $data = array_replace_recursive($data, $_r);
            }

            // If Active not set on create, assuming Active (override in controller/createStepPreSave)
            if (!isset($data[$_modelLower . 'Active'])) {
                $data[$_modelLower . 'Active'] = 1;
            }

            // Save item info, getting ID
            $itemID = $this->$_model->update($data);
            if ($inp_itemID != 'DEFAULT') {
                // Updated (existing ID)
                $itemID = $inp_itemID;
            }

            $this->set($_modelLower . 'ID', $itemID);

            /* Controller specific Step 2: */
            $c::createStepFinish($itemID);

            $this->set('success', TRUE);
        }

        // Get updated list
        self::readall();

        return true;
    }

    /**
     * Controller specific input filtering on save, for use with StepFinish method
     * 
     * e.g. pulls out inp_pageBody and saves that after page has been saved via StepFinish method
     * 
     * @param string $k
     * @param array|string $v
     * 
     * @return boolean True confirms match | False nothing was done
     */
    public static function createStepInput($k, $v)
    {
        // Look in specific Controller
        // EXAMPLE:
//         
//         Page info and body are in separate tables
//        if (in_array($k, array('inp_pageBody'))) {
//            self::$staticData[$k] = $v;
//            return true;
//        }
//        return false;
    }

    /**
     * Controller specific pre-save method
     * 
     * e.g. inserts controller specific data such as page datetime, isActive
     * 
     * @return array data to be merged into model->save($data)
     */
    public static function createStepPreSave()
    {
        // Look in specific Controller
        // EXAMPLE:
//        $data = array();
//        $data['pageActive'] = 1;
//        $data['userID'] = $_SESSION['user']['userID'];
//        
//        // Post time
//        $now                          = Utility::getDateTimeUTC();
//        $data['pageDatePublished']    = $now;
//        $data['pageDateLastReviewed'] = $now;
//        $data['pageDateLastUpdated']  = $now;
//        
//        return $data;
    }

    /**
     * Controller specific finish Create step after first input save
     * 
     * e.g. use self::$staticData in other model save methods (page->body)
     * 
     * @param string $id ID of parent item
     * 
     * @return boolean
     */
    public static function createStepFinish($id)
    {
        // Look in specific Controller
        // EXAMPLE:
//        // Save page body
//        $bodyType = $this->Page->getPageTypeID();
//        $bodyContentID = $this->Page->saveBodyContents($id, $bodyType, self::$staticData['inp_pageBody'], $_SESSION['user']['userID']);
//        $this->set('bodyContentID', $bodyContentID);
//        // Set previous bodies to inactive
//        $this->Page->setBodyContentActive($id, $bodyType, $bodyContentID);
//        return true;
    }

    /**
     * Controller specific finish Delete step after first input delete
     */
    public static function deleteStepFinish($args = NULL)
    {
        // Look in specific Controller
    }

    /**
     * Read
     * 
     * @param array $args
     * @return boolean
     */
    public function read($args)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);

        // Back to ViewAll if item doesn't exist
        $ID = self::itemExists($args);

        return true;
    }

    /**
     * ReadALl
     * 
     * @param array $args
     * @return boolean
     */
    public function readall($prepareForm = TRUE)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);

        $items = self::callModelFunc('getAll');
        
        $this->set($this->controller, $items);

        if ($prepareForm === TRUE) {
            // Prepare Create Form
            self::prepareForm();
        }

        return true;
    }

    /**
     * Update
     * 
     * @param array $args
     * @return boolean
     */
    public function update($args = NULL)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);

        // Load Item or Redirect to ViewAll if item doesn't exist
        $ID = self::itemExists($args);

        if ($this->step == 1) {
            // Loads view all list
            self::readall(FALSE);

            // Prepare Create Form (custom controller override or default)
            $_m = $this->model;
            $_c = $_m . "sController";
            #$cont = new $_c($this->controller, $this->model, NULL, 'json');
            if (method_exists($_c, 'prepareFormCustom')) {
                $_c::prepareFormCustom($ID, $this->template->data);
            } else {
                self::prepareForm($ID);
            }

            return true;
        } elseif ($this->step == 2) {
            // Use create method to update existing
            $res = self::create();
            $this->set('success', $res);
            return $res;
        }
    }

    /**
     * Delete
     * 
     * @param array $args
     * @return boolean
     */
    public function delete($args = NULL)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);

        // Load Item or Redirect to ViewAll if item doesn't exist
        $ID = self::itemExists($args);

        if ($this->step == 1) {
            // [] Confirm deletion ** should be done by JS by now - should not get here **
            // Prepare Delete Form
            //self::prepareForm($ID);
            $res = TRUE;
        } elseif ($this->step == 2) {

            // Must delete child-relation objects prior to main item...

            /* Controller specific: */
            $c = $this->model . "sController";
            $c::deleteStepFinish($ID);

            // Delete item
            $idColName = strtolower($this->model) . 'ID';
            $function  = 'delete';
            $args      = array($idColName => $ID);
            $res       = self::callModelFunc($function, $args);

            $this->set('success', $res);
        }

        // Loads view all list
        self::readall();

        return $res;
    }

    /**
     * Calls method on specified model passing in args
     * 
     * @param string $function
     * @param array $args
     * @param string $model
     * 
     * @return array
     */
    private function callModelFunc($function, $args = NULL, $model = NULL)
    {
        if ($model == NULL) {
            $model = $this->model;
        }
        return call_user_func_array(array($this->$model, $function), array($args));
    }

    /**
     * Ensure item exists (and loads it if found) otherwise returns to readall list
     * 
     * @uses Header
     * 
     * @param array|string $args
     */
    protected function itemExists($args)
    {
        $ID        = FALSE;
        $idColName = strtolower($this->model) . 'ID';

        if ($this->step == 1) {
            // Step 1 gets ID from args
            if (is_array($args)) {
                $ID = $args[0];
            } else {
                $ID = $args;
            }
        } elseif ($this->step == 2) {
            // Step 2 gets ID from POST
            $ID = $_POST['inp_' . $idColName];
        }

        // Confirm this ID exists (load item if found)
        if (!empty($ID)) {
            $item = self::callModelFunc('getWhere', array($idColName => $ID));
            if (!empty($item)) {

                $this->set($idColName, $ID);

                /* Controller specific: */
                $model = $this->model;
                $item  = $model::LoadExtendedItem($item); //."sController";
                // Gets/sets input data from post, must begin with "inp_"
                foreach ($item as $k => $v) {
                    $this->set("inp_" . $k, $v);
                }

                $this->set(strtolower($this->model), $item);
                return $ID;
            }
        }

        // Not found, return to readall page
        header('Location: /' . $this->controller . '/readall');
        exit(0);
    }

}