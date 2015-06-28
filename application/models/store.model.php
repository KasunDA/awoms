<?php

class Store extends Model
{

    /**
     * Log Level
     *
     * @var int $logLevel Config log level; 0 would always be logged, 9999 would only be logged in Dev
     */
    protected static $logLevel = 10;

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
            Errors::debugLogger("Store Owner is logged on... appending AclWhere...", Store::$logLevel);
            $aclWhere['where'] = array('ownerID' => $_SESSION['user']['userID']);
            $aclWhere['in']    = NULL;
        }

        return parent::getWhere($where, $cols, $order, $aclWhere, $in, $loadChildren);
    }

    /**
     * Load additional model specific info when getWhere/getSingle is called
     * Loads children items
     *
     * @param array $item
     */
    public static function LoadExtendedItem($item)
    {
        Errors::debugLogger(__METHOD__, Store::$logLevel);
        if (empty($item['address']))
        {
            if (!empty($item['addressID']))
            {
                // Get Addresses belonging to store
                Errors::debugLogger(__METHOD__.": Get Addresses belonging to store: (addressID: ".$item['addressID'].")...",Store::$logLevel);
                $Address         = new Address();
                $item['address'] = $Address->getSingle(array('addressID' => $item['addressID']));
            } else {
                Errors::debugLogger(__METHOD__.": WARNING: addressID is empty!");
            }
        }

        // Get Domains belonging to store
        Errors::debugLogger(__METHOD__.": Get Domains belonging to store: (brandID: ".$item['brandID']." storeID: ".$item['storeID'].")...",Store::$logLevel);
        $Domain          = new Domain();
        $item['domains'] = $Domain->getWhere(array('brandID' => $item['brandID'],
            'storeID' => $item['storeID']));
        Errors::debugLogger(__METHOD__.": (Domain count: ".count($item['domains']).")", Store::$logLevel);

        if (empty($item['services']))
        {
            // Get Services associated to store
            Errors::debugLogger(__METHOD__.": Get Services associated to store: (storeID: ".$item['storeID'].")...", Store::$logLevel);
            $item['services'] = array();
            $Service          = new Service();
            $serviceIDs       = $Service->select("*", array('storeID' => $item['storeID']), NULL, "refStoreServices");
            if (!empty($serviceIDs))
            {
                foreach ($serviceIDs as $serviceID)
                {
                    $item['services'][] = $Service->getSingle(array('serviceID' => $serviceID['serviceID']));
                }
                $sortArray = array();
                foreach($item['services'] as $s){
                    foreach($s as $key=>$value){
                        if(!isset($sortArray[$key])){
                            $sortArray[$key] = array();
                        }
                        $sortArray[$key][] = $value;
                    }
                }
                $orderby = "serviceName"; //change this to whatever key you want from the array
                array_multisort($sortArray[$orderby],SORT_ASC,$item['services']);
                Errors::debugLogger(__METHOD__.": (Service count: ".count($item['services']).")", Store::$logLevel);
            }
        }
        return $item;
    }

    /**
     * Deletes model and any children items
     *
     * @param array $item
     */
    public static function DeleteExtendedItem($item)
    {
        Errors::debugLogger(__METHOD__);
        if (!empty($item['addressID']))
        {
            // Delete Addresses belonging to store
            Errors::debugLogger(__METHOD__.": Delete Addresses belonging to store: (addressID: ".$item['addressID'].")...");
            $Address         = new Address();
            $Address->delete(array('addressID' => $item['addressID']));
        }

        // Delete Domains belonging to store
        Errors::debugLogger(__METHOD__.": Delete Domains belonging to store: (brandID: ".$item['brandID']." storeID: ".$item['storeID'].")...");
        $Domain          = new Domain();
        $Domain->delete(array('brandID' => $item['brandID'],
            'storeID' => $item['storeID']));

        if (!empty($item['services']))
        {
            // Delete Service associates belonging to store
            Errors::debugLogger(__METHOD__.": Delete Service associates belonging to store: (storeID: ".$item['storeID'].")...");
            $Service          = new Service();
            $Service->delete(array('storeID' => $item['storeID']) , "refStoreServices");
        }
        return $item;
    }

}