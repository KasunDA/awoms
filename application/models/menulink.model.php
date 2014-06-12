<?php

class MenuLink extends Model
{
    protected static function getMenuLinkColumns()
    {
        $cols = array('linkID', 'menuID', 'sortOrder', 'parentLinkID', 'display', 'url', 'linkActive');
        return $cols;
    }
}