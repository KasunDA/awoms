<?php

class Domain extends Model
{
    protected static function getColumns()
    {
        $cols = array('domainID', 'brandID', 'domainName', 'domainActive', 'parentDomainID', 'storeID');
        return $cols;
    }

    public function getWhere($where = NULL, $cols = NULL, $order = NULL, $aclWhere = NULL, $in = NULL, $loadChildren = FALSE)
    {
        if ($order == NULL) {
            $order = "brandID, storeID, parentDomainID, domainName";
        }
        return parent::getWhere($where, $cols, $order, $aclWhere, $in, $loadChildren);
    }

    /**
     * Load additional model specific info when getWhere is called
     * 
     * @param type $item
     */
    public static function LoadExtendedItem($item)
    {
//        if (empty($item['brand']))
//        {
//            $Brand         = new Brand();
//            $b             = $Brand->getSingle(array('brandID'     => $item['brandID'], 'brandActive' => 1));
//            $item['brand'] = $b;
//        }

        if (empty($item['rewriteRules'])) {
            $RewriteMapping       = new RewriteMapping();
            $rw                   = $RewriteMapping->getWhere(array('domainID' => $item['domainID']));
            $item['rewriteRules'] = $rw;
        }

        if (!empty($item['storeID']) && empty($item['store'])) {
            $Store         = new Store();
            $s             = $Store->getSingle(array('storeID' => $item['storeID']));
            $item['store'] = $s;
        }

        return $item;
    }

}