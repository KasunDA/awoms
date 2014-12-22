<?php

class Brand extends Model
{
    protected static function getColumns()
    {
        $cols = array('brandID', 'brandName', 'brandActive', 'brandLabel', 'brandEmail', 'activeTheme', 'addressID', 'cartID');
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
                    DELETE FROM UserSettings
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
        $m->delete(array('brandID' => $ID), 'Brands');
        
        return true;
    }

}