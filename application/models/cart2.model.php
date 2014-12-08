<?php

/**
 * Cart
 */
class Cart2 extends Model
{
    /**
     * @var array Class data
     */
    protected $data = array();
    
    /**
     * Define table columns
     * 
     * @return array
     */
    protected static function getColumns()
    {
        $cols = array('cartID', 'cartName', 'cartActive', 'cartNotes', 'cartTheme', 'addressID',
            'emailOrders', 'emailContact', 'emailErrors',
            'cartPublicKey', 'termsOfService',
            'storefrontCarousel', 'storefrontCategories', 'storefrontDescription');
        return $cols;
    }

    /**
     * Returns all items of model
     * 
     * @param array|string $cols Columns to return if not *
     * @param string $order Order to return (col names)
     * 
     * @return array
     */
    public function getWhere($where = NULL, $cols = NULL, $order = NULL, $aclWhere = NULL, $in = NULL, $loadChildren = FALSE)
    {
        if ($order == NULL) {
            $order = "cartName";
        }
        return parent::getWhere($where, $cols, $order, $aclWhere, $in, $loadChildren);
    }

    /**
     * Load additional model specific info when getWhere/getSingle is called
     * 
     * @param array $item
     */
    public static function LoadExtendedItem($item)
    {
        // Load linked or child items if necessary
//        $Child      = new Child();
//        $item['children'] = $Child->getWhere(array('parentID'     => $item['exampleID']));
        return $item;
    }

}