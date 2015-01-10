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

        /*         * ******
         * USERGROUPS
         * ***** */
        $Usergroup = new Usergroup();
        $ACL       = new ACL();

        // Create new Admin group
        $data        = array('brandID'         => $id,
            'usergroupName'   => 'Administrators',
            'usergroupActive' => 1);
        $usergroupID = $Usergroup->update($data);

        // Assign new Admin group default ACL
        if ($id == 1)
        {
            // Main brand only is allowed to create brands, acl
            ACL::UpdateAccess($id, $usergroupID, NULL, 'brands', 1, 1, 1, 1);
            ACL::UpdateAccess($id, $usergroupID, NULL, 'acls', 1, 1, 1, 1);
        }

        ACL::UpdateAccess($id, $usergroupID, NULL, 'services', 1, 1, 1, 1);
        ACL::UpdateAccess($id, $usergroupID, NULL, 'files', 1, 1, 1, 1);

        ACL::UpdateAccess($id, $usergroupID, NULL, 'stores', 1, 1, 1, 1);
        ACL::UpdateAccess($id, $usergroupID, NULL, 'domains', 1, 1, 1, 1);

        ACL::UpdateAccess($id, $usergroupID, NULL, 'menus', 1, 1, 1, 1);

        ACL::UpdateAccess($id, $usergroupID, NULL, 'usergroups', 1, 1, 1, 1);
        ACL::UpdateAccess($id, $usergroupID, NULL, 'users', 1, 1, 1, 1);

        ACL::UpdateAccess($id, $usergroupID, NULL, 'pages', 1, 1, 1, 1);
        ACL::UpdateAccess($id, $usergroupID, NULL, 'articles', 1, 1, 1, 1);
        ACL::UpdateAccess($id, $usergroupID, NULL, 'comments', 1, 1, 1, 1);

        // Create new Store Owners group
        $data        = array('brandID'         => $id,
            'usergroupName'   => 'Store Owners',
            'usergroupActive' => 1);
        $usergroupID = $Usergroup->update($data);

        // Assign new Store Owners group default ACL
        ACL::UpdateAccess($id, $usergroupID, NULL, 'files', 1, 1, 1, 1);
        ACL::UpdateAccess($id, $usergroupID, NULL, 'stores', 0, 1, 1, 0); // RU
        ACL::UpdateAccess($id, $usergroupID, NULL, 'domains', 1, 1, 1, 1); // CRUD
        ACL::UpdateAccess($id, $usergroupID, NULL, 'menus', 1, 1, 1, 1);
        ACL::UpdateAccess($id, $usergroupID, NULL, 'usergroups', 1, 1, 1, 1);
        ACL::UpdateAccess($id, $usergroupID, NULL, 'users', 0, 1, 1, 0); // RU
        ACL::UpdateAccess($id, $usergroupID, NULL, 'pages', 1, 1, 1, 1);
        ACL::UpdateAccess($id, $usergroupID, NULL, 'articles', 1, 1, 1, 1);
        ACL::UpdateAccess($id, $usergroupID, NULL, 'comments', 1, 1, 1, 1);

        // ** Create Store Owner User **
        // (skip for first brand)
        if ($id != 1)
        {
            $Brand               = new Brand();
            $brand               = $Brand->getSingle(array('brandID' => $id));
            $username            = $brand['brandName'];
            $username            = preg_replace('~[\W\s0-9]~', '', $username);
            $username            = strtolower($username);
            $User                = new User();
            $user                = array();
            $user['userID']      = "DEFAULT";
            $user['usergroupID'] = $usergroupID;
            $user['userActive']  = 1;
            $user['userName']    = $username;
            $user['passphrase']  = $username;
            $user['userEmail']   = $brand['brandEmail'];
            $User->update($user);
        }

        // Create new Users group
        $data        = array('brandID'         => $id,
            'usergroupName'   => 'Users',
            'usergroupActive' => 1);
        $usergroupID = $Usergroup->update($data);

        // Assign new Users group default ACL
        ACL::UpdateAccess($id, $usergroupID, NULL, 'files', 1, 1, 0, 0);
        ACL::UpdateAccess($id, $usergroupID, NULL, 'pages', 0, 1, 0, 0);
        ACL::UpdateAccess($id, $usergroupID, NULL, 'articles', 0, 1, 0, 0);
        ACL::UpdateAccess($id, $usergroupID, NULL, 'comments', 1, 1, 1, 1);

        /*         * ******
         * MENUS
         * ***** */

        // Menu: Heading Nav
        $data     = array();
        $Menu     = new Menu();
        $menuID   = $Menu->update(array('brandID'        => $id,
            'menuType'       => 'Heading Nav',
            'menuRestricted' => 0,
            'menuName'       => 'Heading Menu',
            'menuActive'     => 1));
        $MenuLink = new MenuLink();

        $data['menuID']       = $menuID;
        $data['sortOrder']    = 0;
        $data['parentLinkID'] = NULL;
        $data['display']      = 'Home';
        $data['url']          = '/';
        $data['linkActive']   = 1;
        $MenuLink->update($data);

        $data['menuID']       = $menuID;
        $data['sortOrder']    = 1;
        $data['parentLinkID'] = NULL;
        $data['display']      = 'About Us';
        $data['url']          = '/about';
        $data['linkActive']   = 1;
        $MenuLink->update($data);

        $data['menuID']       = $menuID;
        $data['sortOrder']    = 2;
        $data['parentLinkID'] = NULL;
        $data['display']      = 'Franchises';
        $data['url']          = '/franchises';
        $data['linkActive']   = 1;
        $MenuLink->update($data);

        $data['menuID']       = $menuID;
        $data['sortOrder']    = 3;
        $data['parentLinkID'] = NULL;
        $data['display']      = 'Store Locator';
        $data['url']          = '/locations';
        $data['linkActive']   = 1;
        $MenuLink->update($data);

        // Menu: Footer Left
        $menuID   = $Menu->update(array('brandID'        => $id,
            'menuType'       => 'Footer Left',
            'menuRestricted' => 0,
            'menuName'       => 'Store Info',
            'menuActive'     => 1));
        $MenuLink = new MenuLink();

        $data['menuID']       = $menuID;
        $data['sortOrder']    = 1;
        $data['parentLinkID'] = NULL;
        $data['display']      = 'About Us';
        $data['url']          = '/about';
        $data['linkActive']   = 1;
        $MenuLink->update($data);

        $data['menuID']       = $menuID;
        $data['sortOrder']    = 2;
        $data['parentLinkID'] = NULL;
        $data['display']      = 'Franchises';
        $data['url']          = '/franchises';
        $data['linkActive']   = 1;
        $MenuLink->update($data);

        $data['menuID']       = $menuID;
        $data['sortOrder']    = 3;
        $data['parentLinkID'] = NULL;
        $data['display']      = 'Store Locator';
        $data['url']          = '/locations';
        $data['linkActive']   = 1;
        $MenuLink->update($data);

        $data['menuID']       = $menuID;
        $data['sortOrder']    = 4;
        $data['parentLinkID'] = NULL;
        $data['display']      = 'Franchise Login';
        $data['url']          = '/owners';
        $data['linkActive']   = 1;
        $MenuLink->update($data);

        // Menu: Footer Middle
        $menuID   = $Menu->update(array('brandID'        => $id,
            'menuType'       => 'Footer Middle',
            'menuRestricted' => 0,
            'menuName'       => 'Site Info',
            'menuActive'     => 1));
        $MenuLink = new MenuLink();

        $data['menuID']       = $menuID;
        $data['sortOrder']    = 1;
        $data['parentLinkID'] = NULL;
        $data['display']      = 'Employment';
        $data['url']          = '/employment';
        $data['linkActive']   = 1;
        $MenuLink->update($data);

        $data['menuID']       = $menuID;
        $data['sortOrder']    = 2;
        $data['parentLinkID'] = NULL;
        $data['display']      = 'Terms & Privacy';
        $data['url']          = '/terms';
        $data['linkActive']   = 1;
        $MenuLink->update($data);

        $data['menuID']       = $menuID;
        $data['sortOrder']    = 3;
        $data['parentLinkID'] = NULL;
        $data['display']      = 'Site Map';
        $data['url']          = '/sitemap';
        $data['linkActive']   = 1;
        $MenuLink->update($data);

        $data['menuID']       = $menuID;
        $data['sortOrder']    = 4;
        $data['parentLinkID'] = NULL;
        $data['display']      = 'Contact Us';
        $data['url']          = '/contact';
        $data['linkActive']   = 1;
        $MenuLink->update($data);

        // Menu: Footer Right
        $menuID               = $Menu->update(array('brandID'        => $id,
            'menuType'       => 'Footer Right',
            'menuRestricted' => 0,
            'menuName'       => 'Departments',
            'menuActive'     => 1));
        $MenuLink             = new MenuLink();
        $data['menuID']       = $menuID;
        $data['sortOrder']    = 0;
        $data['parentLinkID'] = NULL;
        $data['display']      = '& More...';
        $data['url']          = '/';
        $data['linkActive']   = 1;
        $MenuLink->update($data);

        // Menu: (Owners) Heading Nav
        $menuID               = $Menu->update(array('brandID'        => $id,
            'menuType'       => 'Heading Nav',
            'menuRestricted' => 1,
            'menuName'       => 'Store Owners Menu',
            'menuActive'     => 1));
        // -- Menu Links
        $MenuLink             = new MenuLink();
        $data['menuID']       = $menuID;
        $data['sortOrder']    = 0;
        $data['parentLinkID'] = NULL;
        $data['display']      = 'Home';
        $data['url']          = '/';
        $data['linkActive']   = 1;
        $MenuLink->update($data);

        $data['menuID']       = $menuID;
        $data['sortOrder']    = 1;
        $data['parentLinkID'] = NULL;
        $data['display']      = 'Owners Home';
        $data['url']          = '/owners';
        $data['linkActive']   = 1;
        $MenuLink->update($data);

        $data['menuID']       = $menuID;
        $data['sortOrder']    = 2;
        $data['parentLinkID'] = NULL;
        $data['display']      = 'Owners Store';
        $data['url']          = '/cart';
        $data['linkActive']   = 1;
        $MenuLink->update($data);

        /*
        $data['menuID']       = $menuID;
        $data['sortOrder']    = 3;
        $data['parentLinkID'] = NULL;
        $data['display']      = 'Log Out';
        $data['url']          = '/logout';
        $data['linkActive']   = 1;
         */
        $MenuLink->update($data);

        // Menu: (Owners) Your Body
        $menuID               = $Menu->update(array('brandID'        => $id,
            'menuType'       => 'Body',
            'menuRestricted' => 1,
            'menuName'       => 'Your Stores',
            'menuActive'     => 1));
        // -- Menu Links
        $MenuLink             = new MenuLink();
        $data['menuID']       = $menuID;
        $data['sortOrder']    = 0;
        $data['parentLinkID'] = NULL;
        $data['display']      = 'Store Information';
        $data['url']          = '/stores';
        $data['linkActive']   = 1;
        $MenuLink->update($data);

        // Menu: (Owners) Products
        $menuID               = $Menu->update(array('brandID'        => $id,
            'menuType'       => 'Body',
            'menuRestricted' => 1,
            'menuName'       => 'Products',
            'menuActive'     => 1));
        // -- Menu Links
        $MenuLink             = new MenuLink();
        $data['menuID']       = $menuID;
        $data['sortOrder']    = 0;
        $data['parentLinkID'] = NULL;
        $data['display']      = "Owner's Store";
        $data['url']          = '/cart';
        $data['linkActive']   = 1;
        $MenuLink->update($data);

        /*         * ******
         * CART
         * ***** */
        Install::InstallCart(NULL, $id);

        /**
         * Create default Public and Private folders for brand's files
         */
        $p = ROOT.DS.'public'.DS.'file'.DS.'source'.DS.'Brands'.DS.$id.DS.'Public';
        if (!file_exists($p))
        {
            mkdir($p, 0777, true);
        }
        
        $p = ROOT.DS.'public'.DS.'file'.DS.'thumbs'.DS.'Brands'.DS.$id.DS.'Public';
        if (!file_exists($p))
        {
            mkdir($p, 0777, true);
        }
        
        $p = ROOT.DS.'public'.DS.'file'.DS.'source'.DS.'Brands'.DS.$id.DS.'Private';
        if (!file_exists($p))
        {
            mkdir($p, 0777, true);
        }
        
        $p = ROOT.DS.'public'.DS.'file'.DS.'thumbs'.DS.'Brands'.DS.$id.DS.'Private';
        if (!file_exists($p))
        {
            mkdir($p, 0777, true);
        }
        
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