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

        $Brand = new Brand();
        $item['brand'] = $Brand->getWhere(array('brandID' => $item['usergroup']['brandID']));
        
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
        Errors::debugLogger(__METHOD__.': username: '.$username.' brandid: '.BRAND_ID);
        
        #// Get shared brand (brand_id 1) and this brands Usergroups
        $UserGroup = new UserGroup();
        #$groupsA = $UserGroup->getWhere(array('brandID' => 1, 'usergroupActive' => 1));
        #$groupsB = array();
        #if (BRAND_ID != 1)
        #{
            # Only allow logins from groups of matching domain -> brand
            $groups = $UserGroup->getWhere(array('brandID' => BRAND_ID, 'usergroupActive' => 1));
        #}
        #$groups = array_replace_recursive ($groupsA, $groupsB);
        
        if (empty($groups)) return false;

        // Valid login?
        foreach ($groups as $group) {$in[] = $group['usergroupID'];}
        
        $_user = self::getWhere(array('userActive' => 1,
            'userName' => $username,
            'passphrase' => $passphrase,
            'usergroupID' => NULL),
                $in);

        if (empty($_user)) {
            return false;
        }
        $_user = self::LoadExtendedItem($_user);

        return $_user;
    }

}