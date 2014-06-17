<?php

class MenuLink extends Model
{
    protected static function getMenuLinkColumns()
    {
        $cols = array('linkID', 'menuID', 'sortOrder', 'parentLinkID', 'display', 'url', 'linkActive');
        return $cols;
    }
    
    public function update($data, $table = NULL)
    {
        Errors::debugLogger(__METHOD__.': ');
        Errors::debugLogger($data, 90);
        parent::update($data, $table);
    }
}