<?php

class BrandsController extends Controller
{
    public function __construct($controller, $model, $action, $template = NULL)
    {
        parent::__construct($controller, $model, $action, $template);
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
        // Create default usergroups for new brand
        
        $Usergroup = new Usergroup();

        $data = array('brandID' => $id,
            'usergroupName' => 'Administrators',
            'usergroupActive' => 1);
        $Usergroup->update($data);
        
        $data = array('brandID' => $id,
            'usergroupName' => 'Store Owners',
            'usergroupActive' => 1);
        $Usergroup->update($data);
        
        $data = array('brandID' => $id,
            'usergroupName' => 'Users',
            'usergroupActive' => 1);
        $Usergroup->update($data);

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
        $brandsList = $this->Brand->getAll();
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