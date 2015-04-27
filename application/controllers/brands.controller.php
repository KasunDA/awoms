<?php

class BrandsController extends Controller
{

    public function __construct($controller, $model, $action, $template = NULL)
    {
        parent::__construct($controller, $model, $action, $template);
    }

    public static $staticData = array();

    /**
     * Controller specific input filtering on save, for use with StepFinish method
     *
     * @param string $k
     * @param array|string $v
     *
     * @return boolean True confirms match | False nothing was done
     */
    public static function createStepInput($k, $v)
    {
        // Menu links are in separate tables
        if (in_array($k, array('inp_brandID', 'inp_brandName', 'inp_brandEmail')))
        {
            self::$staticData[$k] = $v;
        }
        return false;
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
    public static function createStepFinish($id, $data)
    {
        // We only run these on CREATE not UPDATE
        if (isset(self::$staticData['inp_brandID']) && self::$staticData['inp_brandID'] != 'DEFAULT')
        {
            return true;
        }

        $Brand = new Brand();
        $Brand->create($id);        
        return true;
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
        Errors::debugLogger("SelectedID: $SelectedID");
        $brandsList = $this->Brand->getWhere();
        if (empty($brandsList))
        {
            $brandChoiceList = "<option value=''>-- None --</option>";
        }
        else
        {
            $brandChoiceList = '';
            foreach ($brandsList as $brand)
            {
                $selected = '';
                if ($SelectedID !== FALSE && $SelectedID !== TRUE && $SelectedID !== "ALL" && $brand['brandID'] == $SelectedID)
                {
                    $selected = " selected";
                }
                $brandChoiceList .= "<option value='" . $brand['brandID'] . "'" . $selected . ">" . $brand['brandName'] . "</option>";
            }
        }
        return $brandChoiceList;
    }

    /**
     * Controller specific finish Delete step after first input delete
     */
    public static function deleteStepFinish($args = NULL)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);
    }

}