<?php

class Menu extends Model
{
    protected static function getMenuColumns()
    {
        $cols = array('menuID', 'brandID', 'menuName', 'menuActive');
        return $cols;
    }

    public function saveMenuInfo($data)
    {
        return self::update($data);
    }

    public function getMenuInfo($menuID, $LoadLinks = FALSE)
    {
        $cols  = self::getMenuColumns();
        $where = 'menuID = ' . $menuID;
        $order = "menuName ASC";
        $res   = self::select($cols, $where, $order); // Dereferencing not available until php v5.4-5.5
        if (empty($res)) { return false; }
        if ($LoadLinks)
        {
            $MenuLink = new MenuLink();
            $res['0']['links'] = $MenuLink->getMenuLinks($menuID);
        }
        return $res[0];
    }

    public function getMenuIDs($where = NULL)
    {
        $cols  = 'menuID';
        $order = 'menuID ASC'; // ASC - Oldest first
        return self::select($cols, $where, $order);
    }

    public function getBrandActiveMenu()
    {
        $where = " brandID = ".$_SESSION['brandID']." AND menuActive=1";
        $res = self::getMenuIDs($where);
        if (empty($res)) { return false; }
        return self::getMenuInfo($res[0]['menuID'], TRUE);
    }
}