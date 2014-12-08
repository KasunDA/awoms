<?php

class Brand extends Model
{
    protected static function getColumns()
    {
        $cols = array('brandID', 'brandName', 'brandActive', 'brandLabel', 'brandEmail', 'activeTheme', 'addressID', 'cartID');
        return $cols;
    }

    public function getWhere($where = NULL, $cols = NULL, $order = NULL, $aclWhere = NULL, $in = NULL, $loadChildren = FALSE)
    {
        if ($order == NULL) {
            $order = "brandName";
        }
        return parent::getWhere($where, $cols, $order, $aclWhere, $in, $loadChildren);
    }

    public static function LoadExtendedItem($item)
    {
        if (empty($item['services'])) {
            $Service          = new Service();
            $item['services'] = $Service->getWhere(array('brandID' => $item['brandID']));
        }
        return $item;
    }

}