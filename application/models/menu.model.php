<?php

class Menu extends Model
{
    protected static function getMenuColumns()
    {
        $cols = array('brandID', 'menuID', 'linkID', 'sortOrder', 'parentLinkID', 'display', 'url', 'menuActive', 'linkActive');
        return $cols;
    }

    public function saveMenuInfo($data)
    {
        return self::update($data);
    }

    public function getMenu($menuID)
    {
        $cols  = self::getMenuColumns();
        $where = 'menuID = ' . $menuID;
        $order = " sortOrder, display ASC";
        $res   = self::select($cols, $where, $order); // Dereferencing not available until php v5.4-5.5
        return $res;
    }

    /* DISTINCT */
    public function getMenuIDs($where = NULL)
    {
        $cols  = 'DISTINCT menuID';
        $order = 'menuID DESC';
        return self::select($cols, $where, $order);
    }

    public function getBrandActiveMenu()
    {
        $where = " brandID = ".$_SESSION['brandID']." AND menuActive=1";
        $res = self::getMenuIDs($where);
        return self::getMenu($res[0]['menuID']);
    }

}