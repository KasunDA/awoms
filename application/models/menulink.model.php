<?php

class MenuLink extends Model
{
    protected static function getColumns()
    {
        $cols = array('linkID', 'menuID', 'sortOrder', 'parentLinkID', 'display', 'url', 'linkActive');
        return $cols;
    }

    public function getWhere($where = NULL, $cols = NULL, $order = NULL, $aclWhere = NULL, $in = NULL, $loadChildren = FALSE)
    {
        if ($order == NULL) {
            $order = "menuID, parentLinkID, sortOrder, display, url";
        }
        return parent::getWhere($where, $cols, $order, $aclWhere, $in, $loadChildren);
    }

}