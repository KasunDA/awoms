<?php

class Usergroup extends Model
{
    protected static function getUsergroupColumns()
    {
        $cols = array('usergroupID', 'brandID', 'usergroupName', 'usergroupActive', 'parentUserGroupID');
        return $cols;
    }

}