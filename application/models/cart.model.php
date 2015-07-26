<?php

/**
 * Cart
 */
class Cart extends Model
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

    public static function LoadExtendedItem($item)
    {
        if (empty($item['address'])) {
            $Address = new Address();
            $item['address'] = $Address->getSingle(array('addressID' => $item['addressID']));
        }
        return $item;
    }

}