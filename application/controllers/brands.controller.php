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
        // Create default menu for new brand
        $Menu = new Menu();
        $menuID = $Menu->update(array('brandID' => $id,
            'menuName' => 'Default Menu',
            'menuActive' => 1));
        $MenuLink = new MenuLink();
        $data['menuID']       = $menuID;
        $data['sortOrder']    = 0;
        $data['parentLinkID'] = NULL;
        $data['display']      = 'Home';
        $data['url']          = '/';
        $data['linkActive']   = 1;
        $MenuLink->update($data);
        
        // Create default usergroups for new brand
        $Usergroup = new Usergroup();
        $ACL = new ACL();

        // Create new Admin group
        $data = array('brandID' => $id,
            'usergroupName' => 'Administrators',
            'usergroupActive' => 1);
        $usergroupID = $Usergroup->update($data);
        
        // Assign new Admin group default ACL
        if ($id == 1) {
            // Main brand only is allowed to create brands
            ACL::UpdateAccess($id, $usergroupID, NULL, 'brands', 1, 1, 1, 1);
        } else {
            ACL::UpdateAccess($id, $usergroupID, NULL, 'brands', 0, 0, 0, 0);
        }
        ACL::UpdateAccess($id, $usergroupID, NULL, 'domains', 1, 1, 1, 1);
        ACL::UpdateAccess($id, $usergroupID, NULL, 'menus', 1, 1, 1, 1);
        ACL::UpdateAccess($id, $usergroupID, NULL, 'usergroups', 1, 1, 1, 1);
        ACL::UpdateAccess($id, $usergroupID, NULL, 'users', 1, 1, 1, 1);
        ACL::UpdateAccess($id, $usergroupID, NULL, 'pages', 1, 1, 1, 1);
        ACL::UpdateAccess($id, $usergroupID, NULL, 'articles', 1, 1, 1, 1);
        ACL::UpdateAccess($id, $usergroupID, NULL, 'comments', 1, 1, 1, 1);
        
        
        // Create new Store Owners group
        $data = array('brandID' => $id,
            'usergroupName' => 'Store Owners',
            'usergroupActive' => 1);
        $usergroupID = $Usergroup->update($data);
        
        // Assign new Store Owners group default ACL
        ACL::UpdateAccess($id, $usergroupID, NULL, 'brands', 0, 0, 0, 0);
        ACL::UpdateAccess($id, $usergroupID, NULL, 'domains', 1, 1, 1, 1);
        ACL::UpdateAccess($id, $usergroupID, NULL, 'menus', 1, 1, 1, 1);
        ACL::UpdateAccess($id, $usergroupID, NULL, 'usergroups', 1, 1, 1, 1);
        ACL::UpdateAccess($id, $usergroupID, NULL, 'users', 1, 1, 1, 1);
        ACL::UpdateAccess($id, $usergroupID, NULL, 'pages', 1, 1, 1, 1);
        ACL::UpdateAccess($id, $usergroupID, NULL, 'articles', 1, 1, 1, 1);
        ACL::UpdateAccess($id, $usergroupID, NULL, 'comments', 1, 1, 1, 1);
        

        // Create new Users group
        $data = array('brandID' => $id,
            'usergroupName' => 'Users',
            'usergroupActive' => 1);
        $usergroupID = $Usergroup->update($data);

        // Assign new Users group default ACL
        ACL::UpdateAccess($id, $usergroupID, NULL, 'brands', 0, 0, 0, 0);
        ACL::UpdateAccess($id, $usergroupID, NULL, 'domains', 0, 0, 0, 0);
        ACL::UpdateAccess($id, $usergroupID, NULL, 'menus', 0, 0, 0, 0);
        ACL::UpdateAccess($id, $usergroupID, NULL, 'usergroups', 0, 0, 0, 0);
        ACL::UpdateAccess($id, $usergroupID, NULL, 'users', 0, 0, 0, 0);
        ACL::UpdateAccess($id, $usergroupID, NULL, 'pages', 0, 1, 0, 0);
        ACL::UpdateAccess($id, $usergroupID, NULL, 'articles', 0, 1, 0, 0);
        ACL::UpdateAccess($id, $usergroupID, NULL, 'comments', 1, 1, 1, 1);

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
            $brandChoiceList = "<option value=''>-- None --</option>";
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
    
    /**
     * Controller specific finish Delete step after first input delete
     */
    public static function deleteStepFinish($args = NULL)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);
        $ID       = $args;

        // Session
        $DB = new Database();
        $query = "
            DELETE FROM sessions
            WHERE brandID = :brandID
            ";
        $DB->query($query, array(':brandID' => $ID));
        
        // MessageLog
        $query = "
            DELETE FROM messageLog
            WHERE messageBrandID = :brandID
            ";
        $DB->query($query, array(':brandID' => $ID));

        // Domain
        $Domain = new Domain();
        $Domain->delete(array('brandID' => $ID));
        
        // MenuLinks (Menus)
        $Menu = new Menu();
        $ms = $Menu->getWhere(array('brandID' => $ID));
        $MenuLink = new MenuLink();
        foreach ($ms as $m)
        {
            $MenuLink->delete(array('menuID' => $m['menuID']));
        }
        // Menu
        $Menu->delete(array('brandID' => $ID));
        
        // ACL
        $ACL = new ACL();
        $ACL->delete(array('brandID' => $ID));
        
        // Page
        $Page = new Page();
        $Page->delete(array('brandID' => $ID));
        
        // Article
        $Article = new Article();
        $Article->delete(array('brandID' => $ID));
        
        // Users (Usergroups)
        $Usergroup = new Usergroup();
        $ugs = $Usergroup->getWhere(array('brandID' => $ID));
        $User = new User();
        foreach ($ugs as $ug)
        {
            $User->delete(array('usergroupID' => $ug['usergroupID']));
        }
        // Usergroup
        $Usergroup->delete(array('brandID' => $ID));
        
    }

}