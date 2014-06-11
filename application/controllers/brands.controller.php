<?php

class BrandsController extends Controller
{
    public function __construct($controller, $model, $action, $template = NULL)
    {
        parent::__construct($controller, $model, $action, $template);
    }

    /**
     * Update
     */
    public function update($args)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);
        parent::update($args);

        $this->set('title', 'Brands :: Update');

        $args = func_get_args();
        if (!empty($args)) {
            $ID = $args[0];
            $this->set('brandID', $ID);
        } else {
            header('Location: /brands/readall');
            exit(0);
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
            // Use create method to update existing
            $res = $this->create();
        }

        return $res;
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
        $Brand = new Brand();
        $brandsList = $Brand->getAll();
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