<?php

class MenuLink extends Model
{
    protected static function getMenuLinkColumns()
    {
        $cols = array('linkID', 'menuID', 'sortOrder', 'parentLinkID', 'display', 'url', 'linkActive');
        return $cols;
    }

    public function saveMenuLinkInfo($data)
    {
        return self::update($data);
    }

    public function getMenuLinks($menuID)
    {
        $cols  = self::getMenuLinkColumns();
        $where = 'menuID=' . $menuID . ' AND linkActive=1';
        $order = "sortOrder, display ASC";
        $res   = self::select($cols, $where, $order);
        return $res;
    }

    public function getMenuLinkIDs($where = NULL)
    {
        if ($where == NULL) { $where = "linkActive=1"; }
        $cols  = 'menuID';
        $order = 'menuID DESC';
        return self::select($cols, $where, $order);
    }
    
    public function removeAllMenuLinks($menuID)
    {
        $query = "
            DELETE FROM menuLinks
            WHERE menuID=:menuID";
        $data = array(':menuID' => $menuID);
        $res = $this->query($query, $data);
        return $res;
    }
}