<?php

class BrandsController extends Controller
{
    public function __construct($controller, $model, $action, $template = NULL)
    {
        parent::__construct($controller, $model, $action, $template);
    }

    /**
     * View All
     */
    public function viewall()
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);

        // Template data
        $this->set('title', 'Brands :: View All');

        // Restrict viewing list if non-admin
        $limit = NULL;
        if ($_SESSION['user']['usergroup']['usergroupName'] != "Administrators")
        {
            $limit = "brandActive=1 AND brandID = ".$_SESSION['brandID'];
        }
        
        // Get brand list
        $this->set('brands', $this->getBrands($limit));

        // Prepare Create Form
        parent::prepareForm();
    }

    /**
     * Create
     */
    public function create()
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);

        $this->set('title', 'Brands :: Create');

        // Step 1: Create/Edit form
        if ($this->step == 1) {

            // Prepare Create Form
            parent::prepareForm();
        }

        // Step 2: Save brand
        elseif ($this->step == 2) {

            // Data array to be passed to sql
            $data                = array();
            $data['brandActive'] = 1;

            // Gets input data from post, must begin with "inp_"
            foreach ($_POST as $k => $v) {
                if (!preg_match('/^inp_(.*)/', $k, $m)) {
                    continue;
                }

                $this->set($k, $v);

                // Brand id (new or existing)
                if ($k == 'inp_brandID') {
                    $inp_brandID = $v;
                }

                // Brand info col/data
                $data[$m[1]] = $v;
            }

            // Save brand info, getting ID
            $brandID = $this->Brand->update($data);
            if ($inp_brandID != 'DEFAULT') {
                // Updated (existing ID)
                $brandID = $inp_brandID;
            }

            $this->set('brandID', $brandID);
            $this->set('success', TRUE);
        }

        // Get updated list
        $this->set('brands', $this->getBrands());
    }

    /**
     * Edit
     */
    public function edit()
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);

        $this->set('title', 'Brands :: Edit');

        $args = func_get_args();
        if (!empty($args)) {
            $ID = $args[0];
            $this->set('brandID', $ID);
        }

        $res = TRUE;
        if ($this->step == 1) {
            $brand = $this->Brand->getBrandInfo($ID);
            $this->set('brand', $brand);

            // Get brand list
            $this->set('brands', $this->getBrands());

            // Gets input data from post, must begin with "inp_"
            foreach ($brand as $k => $v) {
                $this->set("inp_" . $k, $v);
            }

            // Prepare Create Form
            parent::prepareForm($ID);
        } elseif ($this->step == 2) {
            // Use create method to edit existing
            $res = $this->create();
        }

        return $res;
    }

    /**
     * Get Brand List
     * 
     * Returns array of all active brands and their info
     * 
     * @return array
     */
    public function getBrands($where = NULL)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);        
        if ($where == NULL) { $where = 'brandActive=1'; }
        $brandIDs = $this->Brand->getBrandIDs($where);
        $brands   = array();
        foreach ($brandIDs as $b) {
            $brand    = $this->Brand->getBrandInfo($b['brandID']);
            $brands[] = $brand;
        }
        return $brands;
    }

    /**
     * Generates <option> list for Brand select list use in template
     * 
     * @example 
     * $brands     = new BrandsController('brands', 'Brand', NULL, 'json');
     * $brandsList = $brands->GetBrandChoiceList();
     */
    public function GetBrandChoiceList($SelectedID = FALSE)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);
        $brandsList = $this->getBrands();
        if (empty($brandsList)) {
            $brandChoiceList = "<option value=''>--None--</option>";
        } else {
            $brandChoiceList = '';
            foreach ($brandsList as $brand) {
                $selected = '';
                if ($SelectedID != FALSE && $SelectedID != "ALL" && $brand['brandID'] == $SelectedID) {
                    $selected = " selected";
                }
                $brandChoiceList .= "<option value='" . $brand['brandID'] . "'" . $selected . ">" . $brand['brandName'] . "</option>";
            }
        }
        return $brandChoiceList;
    }

}