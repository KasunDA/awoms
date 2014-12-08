<?php

class Store extends Model
{

    protected static function getColumns()
    {

        $cols = array(
            // ID
            'storeID', 'brandID', 'cartID', 'ownerID', 'storeNumber', 'storeActive', 'storeTheme', 'storeName',
            // Info
            'addressID', 'coding', 'companyName', 'legalName', 'ein',
            'phone', 'fax', 'tollFree', 'website', 'email', 'emailStoreCorrespondence',
            'bio', 'latitude', 'longitude', // 'pic1', 'pic2', 'pic3', 'cap1', 'cap2', 'cap3',
            'storeToStoreSales', 'bankruptcy', 'turnkey', 'openDate', 'transferDate', 'closeDate', 'terminationDate',
            'facebookURL',
            // Hours
            'hrsMon', 'hrsTue', 'hrsWed', 'hrsThu', 'hrsFri', 'hrsSat', 'hrsSun'
        );
        return $cols;
    }

    /**
     * Returns all items of model (restricted by ACL)
     *
     * @param array|string $cols Columns to return if not *
     * @param string $order Order to return (col names)
     *
     * @return array
     */
    public function getWhere($where = NULL, $cols = NULL, $order = NULL, $aclWhere = NULL, $in = NULL, $loadChildren = FALSE)
    {
        if ($order == NULL)
        {
            $order = "brandID, storeNumber, storeName";
        }

        // ACL: Ensure user only sees items they have permission to
        $aclWhere          = array();
        $aclWhere['where'] = NULL;
        $aclWhere['in']    = NULL;

        // Admin: All
        // Store Owner: stores.ownerID = users.userID
        // Else: stores.brandID = users.brandID (done via model.class.aclWhere)

        if (!empty($_SESSION['user_logged_in'])
            && $_SESSION['user']['usergroup']['usergroupName'] == "Store Owners")
        {
            Errors::debugLogger("Store Owner is logged on... appending AclWhere...",10);
            $aclWhere['where'] = array('ownerID' => $_SESSION['user']['userID']);
            $aclWhere['in']    = NULL;
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
//        if (empty($item['brand']))
//        {
//            $Brand         = new Brand();
//            $item['brand'] = $Brand->getSingle(array('brandID' => $item['brandID']));
//        }
        // Address
        if (empty($item['address']))
        {
            $item['address'] = NULL;
            if (!empty($item['addressID']))
            {
                $Address         = new Address();
                $item['address'] = $Address->getSingle(array('addressID' => $item['addressID']));
            }
        }

        // Domain
        if (empty($item['address']))
        {
            $Domain          = new Domain();
            $item['domains'] = $Domain->getWhere(array('brandID' => $item['brandID'],
                'storeID' => $item['storeID']));
        }

        // Services
        if (empty($item['services']))
        {
            $item['services'] = array();
            $Service          = new Service();
            $serviceIDs       = $Service->select("*", array('storeID' => $item['storeID']), NULL, "refStoreServices");
            foreach ($serviceIDs as $serviceID)
            {
                $item['services'][] = $Service->getSingle(array('serviceID' => $serviceID['serviceID']));
            }
            #$where = array('storeID' => $item['storeID']);
            #$s     = $Brand->select("*", $where, NULL, 'storeServices');
            #if (!empty($s)) {
            #$item['services'] = $s[0];
            #} else {
            #$item['services'] = $s;
            #}
        }
        return $item;
    }

}