<?php

class User extends Model
{
    protected static function getUserColumns()
    {
        $cols = array('userID', 'usergroupID', 'userName', 'userActive', 'userEmail', 'passphrase');
        return $cols;
    }

    /**
     * Load additional model specific info when getWhere is called
     * 
     * @param type $ID
     */
    public static function LoadExtendedItem($item)
    {
        $Usergroup = new Usergroup();
        $item['usergroup'] = $Usergroup->getWhere(array('usergroupID' => $item['usergroupID']));
        return $item;
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
        
        // Get shared brand (brand_id 1) and this brands Usergroups
        $UserGroup = new UserGroup();
        $groupsA = $UserGroup->getWhere(array('brandID' => 1, 'usergroupActive' => 1));
        $groupsB = array();
        if (BRAND_ID != 1)
        {
            $groupsB = $UserGroup->getWhere(array('brandID' => BRAND_ID, 'usergroupActive' => 1));
        }
        $groups = array_replace_recursive ($groupsA, $groupsB);
        
        if (empty($groups)) return false;

        // Valid login?
        $cols  = "userID";
        $where = "
            userActive = 1
            AND userName = '" . $username . "'
            AND passphrase = '" . $passphrase . "'
            AND usergroupID ";
        foreach ($groups as $group) {$in[] = $group['usergroupID'];}

        $r     = self::select($cols, $where, NULL, NULL, $in);

        if (empty($r[0]['userID'])) {
            return false;
        }

        $_u = self::getWhere(array('userID' => $r[0]['userID']));
        $_u = self::LoadExtendedItem($_u);
        return $_u;
    }

}