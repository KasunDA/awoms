<?php

class Usergroup extends Model
{
    protected static function getColumns()
    {
        $cols = array('usergroupID', 'brandID', 'usergroupName', 'usergroupActive', 'parentUserGroupID');
        return $cols;
    }

    public function getWhere($where = NULL, $cols = NULL, $order = NULL, $aclWhere = NULL, $in = NULL, $loadChildren = FALSE)
    {
        if ($order == NULL) {
            $order = "brandID, parentUserGroupID, usergroupName";
        }
        return parent::getWhere($where, $cols, $order, $aclWhere, $in, $loadChildren);
    }

    /**
     * Load additional model specific info when getWhere is called
     * 
     * @param type $ID
     */
    public static function LoadExtendedItem($item)
    {
        //$Brand         = new Brand();
        //$item['brand'] = $Brand->getSingle(array('brandID' => $item['brandID']));
        return $item;
    }

}