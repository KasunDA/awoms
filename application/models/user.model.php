<?php

class User extends Model
{
    protected static function getUserColumns()
    {
        $cols = array('userID', 'usergroupID', 'userName', 'userActive', 'userEmail', 'passphrase');
        return $cols;
    }

    /**
     * Validates user login from db, returning false or user info
     * 
     * @param type $username
     * @param type $passphrase
     * @return array|boolean Array - valid login | False - invalid login
     */
    public function ValidateLogin($username, $passphrase)
    {
        Errors::debugLogger(__METHOD__);

        
        $UserGroup = new UserGroup();
        $groupsA = $UserGroup->getWhere(array('brandID' => 1, 'usergroupActive' => 1));
        $groupsB = $UserGroup->getWhere(array('brandID' => BRAND_ID, 'usergroupActive' => 1));
        $groups = array_merge($groupsA, $groupsB);
        

        if (empty($groups)) return false;
        
        /*
         * 
         * 
         * 
         * 
         */
        
        
        // Get brands usergroups
        $cols             = "usergroupID";
        /**
         * The hard coded 1 here allow for shared usergroups amongst all brands (groups created under main brand id 1 are shared)
         * If the brand has its own custom groups it will look for them as well
         */
        $sharedBrandID    = 1;
        $where            = "brandID IN (" . $sharedBrandID . ", " . BRAND_ID . ")
          AND usergroupActive = 1";
        $thisBrandsGroups = self::select($cols, $where, NULL, 'usergroups');
        if (empty($thisBrandsGroups)) return false;


        $w = "usergroupID IN (";
        foreach ($thisBrandsGroups as $group) {
            $w .= $group['usergroupID'] . ",";
        }
        $w = rtrim($w, ",");
        $w .= ")";

        // Valid login?
        $cols  = "userID";
        $where = $w . "
            AND userActive = 1
            AND userName = '" . $username . "'
            AND passphrase = '" . $passphrase . "'";
        $r     = self::select($cols, $where);

        if (empty($r[0]['userID'])) {
            return false;
        }

        $_u = self::getWhere(array('userID' => $r[0]['userID']));
        
        $UserGroup = new UserGroup();
        $_u[0]['usergroup'] = $UserGroup->getWhere(array('usergroupID' => $_u[0]['usergroupID']));
        
        var_dump($_u[0]);
        
        return $_u[0];
    }

}