<?php

class Service extends Model
{

    protected static function getColumns()
    {
        $cols = array('serviceID', 'brandID', 'serviceName', 'serviceDescription', 'serviceActive');
        return $cols;
    }

    public function getWhere($where = NULL, $cols = NULL, $order = NULL, $aclWhere = NULL, $in = NULL, $loadChildren = FALSE)
    {
        if ($order == NULL)
        {
            $order = "brandID, serviceName";
        }
        return parent::getWhere($where, $cols, $order, $aclWhere, $in, $loadChildren);
    }

    public static function LoadExtendedItem($item)
    {
//        if (empty($item['brand'])) {
//            $Brand         = new Brand();
//            $item['brand'] = $Brand->getSingle(array('brandID' => $item['brandID']));
//        }
        return $item;
    }

}