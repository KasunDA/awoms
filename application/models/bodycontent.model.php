<?php

class BodyContent extends Model
{
    protected static function getBodyContentColumns()
    {
        $cols = array('bodyContentID', 'parentItemID', 'parentItemTypeID', 'bodyContentActive', 'bodyContentDateModified', 'bodyContentText', 'userID');
        return $cols;
    }
}