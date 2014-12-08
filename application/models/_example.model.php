<?php

class Example extends Model
{

    protected static function getColumns()
    {
        $cols = array('exampleID', 'brandID', 'exampleName');
        return $cols;
    }

    public function getWhere($where = NULL, $cols = NULL, $order = NULL, $aclWhere = NULL, $in = NULL, $loadChildren = FALSE)
    {
        if ($order == NULL) {
            $order = "brandID, menuName";
        }
        return parent::getWhere($where, $cols, $order, $aclWhere, $in, $loadChildren);
    }

    public static function LoadExtendedItem($item)
    {
        // Load linked or child items if necessary
//        $Child      = new Child();
//        $item['children'] = $Child->getWhere(array('parentID'     => $item['exampleID']));
        return $item;
    }

}