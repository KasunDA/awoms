<?php

class Domain extends Model
{
    protected static function getDomainColumns()
    {
        $cols = array('domainID', 'brandID', 'domainName', 'domainActive', 'parentDomainID');
        return $cols;
    }

    /**
     * Load additional model specific info when getWhere is called
     * 
     * @param type $item
     */
    public static function LoadExtendedItem($item)
    {
        $Brand         = new Brand();
        $b = $Brand->getSingle(array('brandID'     => $item['brandID'], 'brandActive' => 1));
        $item['brand'] = $b;

        return $item;
    }

}