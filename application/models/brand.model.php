<?php

class Brand extends Model
{
    protected static function getColumns()
    {
        $cols = array('brandID', 'brandName', 'brandActive', 'brandLabel', 'brandMetaTitle', 'brandMetaDescription', 'brandMetaKeywords', 'brandEmail', 'activeTheme', 'addressID', 'cartID', 'brandFavIcon');
        return $cols;
    }

    public function getWhere($where = NULL, $cols = NULL, $order = NULL, $aclWhere = NULL, $in = NULL, $loadChildren = FALSE)
    {
        if ($order == NULL) {
            $order = "brandName";
        }
        return parent::getWhere($where, $cols, $order, $aclWhere, $in, $loadChildren);
    }

    public static function LoadExtendedItem($item)
    {
        if (empty($item['services'])) {
            $Service          = new Service();
            $item['services'] = $Service->getWhere(array('brandID' => $item['brandID']));
        }
        return $item;
    }

    /**
     * Used for CREATING brands extended items
     * 
     * @param int $id Brand ID
     * @param array $data Brand data
     */
    public function create($id, $data)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);
        if (empty($id)) {
            Errors::debugLogger("Missing BrandID");
        }

        $this->createBrandUsergroups($id);
        $this->createBrandMenus($id);
        $this->createBrandFolders($id);
        $this->createBrandDomains($data);
        // Create default cart for Brand
        Install::InstallCart(NULL, $id);
        return true;
    }

    public function delete($data, $table = NULL, $limit = false)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);
        
        if (is_array($data))
        {
            $ID = $data['brandID'];
        } else {
            $ID = $data;
        }
        
        if (empty($ID)) {
            Errors::debugLogger("Missing BrandID");
        }
        
        $DB = new \Database();
        $Menu = new Menu();
        $MenuLink = new MenuLink();
        $Domain = new Domain();
        $RewriteMapping = new RewriteMapping();
        $ACL = new ACL();
        $Page = new Page();
        $Article = new Article();
        $Usergroup = new Usergroup();
        $User = new User();
        $BodyContent = new BodyContent();
        $Store = new Store();
        $Service = new Service();

        // Session
        Errors::debugLogger(sprintf("Deleting Brand #%s", $ID));
        Errors::debugLogger("Deleting Sessions...");
        $query = "
            DELETE FROM sessions
            WHERE brandID = :brandID
            ";
        $DB->query($query, array(':brandID' => $ID));

        // MessageLog
        Errors::debugLogger("Deleting MessageLogs...");
        $query = "
            DELETE FROM messageLog
            WHERE brandID = :brandID
            ";
        $DB->query($query, array(':brandID' => $ID));

        // Domains
        Errors::debugLogger("Deleting Domains...");
        $domains        = $Domain->getWhere(array('brandID' => $ID));
        foreach ($domains as $domain)
        {
            $rewriteRules = $RewriteMapping->getWhere(array('domainID' => $domain['domainID']));
            Errors::debugLogger("Deleting RewriteRules...");
            foreach ($rewriteRules as $rewriteRule)
            {
                $RewriteMapping->removeRewriteRule($rewriteRule['aliasURL'], $domain['domainName'], $domain['domainID']);
            }
        }
        $Domain->delete(array('brandID' => $ID));

        // Menus
        Errors::debugLogger("Deleting Menus...");
        $ms       = $Menu->getWhere(array('brandID' => $ID));
        Errors::debugLogger("Deleting Menulinks...");
        foreach ($ms as $m)
        {
            $MenuLink->delete(array('menuID' => $m['menuID']));
        }
        // Menu
        $Menu->delete(array('brandID' => $ID));

        // ACL
        Errors::debugLogger("Deleting ACLs...");
        $ACL->delete(array('brandID' => $ID));

        // Page
        Errors::debugLogger("Deleting Pages...");
        $Page->delete(array('brandID' => $ID));

        // Article
        Errors::debugLogger("Deleting Articles...");
        $Article->delete(array('brandID' => $ID));

        // Users (Usergroups)
        Errors::debugLogger("Deleting Users...");
        $ugs       = $Usergroup->getWhere(array('brandID' => $ID));
        Errors::debugLogger("Deleting Usergroups...");
        foreach ($ugs as $ug)
        {
            Errors::debugLogger("UsergroupID: " . $ug['usergroupID']);
            
            // Get list of users in usergroup
            $us = $User->getWhere(array('usergroupID' => $ug['usergroupID']));
            foreach ($us as $u)
            {
                Errors::debugLogger("UserID: " . $u['userID']);
                
                // Body Contents (Per User)
                Errors::debugLogger("Deleting BodyContents for user...");
                $BodyContent->delete(array('userID' => $u['userID']));

                // Stores
                Errors::debugLogger("Deleting Stores for user...");
                $ss = $Store->getWhere(array('ownerID' => $u['userID']));
                foreach ($ss as $s)
                {
                    // Services
                    $query = "
                        DELETE FROM refStoreServices
                        WHERE storeID = :storeID
                        ";
                    $DB->query($query, array(':storeID' => $s['storeID']));
                    
                    Errors::debugLogger("Deleting Service for store...");
                    $Service->delete(array('brandID' => $s['brandID']));
                }
                
                // Stores (Per User)
                $Store->delete(array('ownerID' => $u['userID']));
                
                // User Settings
                Errors::debugLogger("Deleting User Settings for user...");
                $query = "
                    DELETE FROM userSettings
                    WHERE userID = :userID
                    ";
                $DB->query($query, array(':userID' => $u['userID']));
            }
            $User->delete(array('usergroupID' => $ug['usergroupID']));
        }
        // Usergroup
        $Usergroup->delete(array('brandID' => $ID));
        
        // Store (with no users tied)
        $Store->delete(array('brandID' => $ID));
        
        // Brand
        $m = new Model();
        $m->delete(array('brandID' => $ID), 'brands');
        
        return true;
    }

    /**
     * Create new brands usergroups
     * 
     * @param int $id
     */
    public function createBrandUsergroups($id)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);
        if (empty($id)) {
            Errors::debugLogger("Missing BrandID");
        }

        /********
         * USERGROUPS
         *******/
        $Usergroup = new Usergroup();
        $ACL       = new ACL();

        // Only main brand will have Administrators group
        if ($id == 1)
        {
            $data        = array('brandID'         => $id,
                'usergroupName'   => 'Administrators',
                'usergroupActive' => 1);
            $usergroupID = $Usergroup->update($data);

            // Main brand only is allowed to create brands, acl
            ACL::UpdateAccess($id, $usergroupID, NULL, 'brands', 1, 1, 1, 1);
            ACL::UpdateAccess($id, $usergroupID, NULL, 'acls', 1, 1, 1, 1);

            // Full permissions for rest of standard items
            ACL::UpdateAccess($id, $usergroupID, NULL, 'carts', 1, 1, 1, 1);

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
        }

        // Create new Store Owners group
        $data        = array('brandID'         => $id,
            'usergroupName'   => 'Store Owners',
            'usergroupActive' => 1);
        $usergroupID = $Usergroup->update($data);

        // Assign new Store Owners group default ACL
        ACL::UpdateAccess($id, $usergroupID, NULL, 'carts', 0, 1, 0, 0); // R
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

        return true;
    }

    /**
     * Creates new brands menus
     *
     * @param int $id Brand ID
     */
    public function createBrandMenus($id)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);
        if (empty($id)) {
            Errors::debugLogger("Missing BrandID");
        }
        /********
         * MENUS
         *******/

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

        return true;
    }

    /**
     * Creates new brands folders
     * 
     * @param int $id Brand ID
     */
    public function createBrandFolders($id)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);
        if (empty($id)) {
            Errors::debugLogger("Missing BrandID");
        }
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
     * Creates brands domains
     *
     * @param array $data Brand data
     */
    public function createBrandDomains($data)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);
        if (empty($data['brandID'])) {
            Errors::debugLogger("Missing BrandID");
        }
        if (empty($data['domains'])) {
            Errors::debugLogger("Missing Domains");
            return;
        }

        // Domains
        $Domain = new Domain();
        foreach ($data['domains'] as $domainName)
        {
            $domain['brandID']      = $data['brandID'];
            $domain['domainName']   = $domainName;
            $domain['domainActive'] = 1;
            $Domain->update($domain);
        }
    }

}