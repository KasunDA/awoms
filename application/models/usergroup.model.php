<?php

class Usergroup extends Model
{
    protected static function getUsergroupColumns()
    {
        $cols = array('usergroupID', 'brandID', 'usergroupName', 'usergroupActive', 'parentUserGroupID');
        return $cols;
    }

    /**
     * Load additional model specific info when getWhere is called
     * 
     * @param type $ID
     */
    public static function LoadExtendedItem($item)
    {
        $Brand = new Brand();
        $item['brand'] = $Brand->getSingle(array('brandID' => $item['brandID']));
        return $item;
    }
}