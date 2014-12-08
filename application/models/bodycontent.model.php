<?php

class BodyContent extends Model
{
    protected static function getBodyContentColumns()
    {
        $cols = array('bodyContentID', 'parentItemID', 'parentItemTypeID', 'bodyContentActive', 'bodyContentDateModified', 'bodyContentText', 'userID');
        return $cols;
    }
    
    public function getWhere($where = NULL, $cols = NULL, $order = NULL, $aclWhere = NULL, $in = NULL, $loadChildren = FALSE)
    {
        if ($order == NULL)
        {
            $order = "parentItemID, bodyContentDateModified";
        }
        return parent::getWhere($where, $cols, $order, $aclWhere, $in, $loadChildren);
    }
}