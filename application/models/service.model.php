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

    public function delete($data, $table = NULL, $limit = false)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);

        if (is_array($data))
        {
            $ID = $data['serviceID'];
        } else {
            $ID = $data;
        }

        if (empty($ID)) {
            Errors::debugLogger("Missing ServiceID");
        }

        $DB = new \Database();
        Errors::debugLogger("Deleting service references...");
        $query = "
            DELETE FROM refStoreServices
            WHERE serviceID = :serviceID
            ";
        $DB->query($query, array(':serviceID' => $ID));

        Errors::debugLogger("Deleting service...");
        $m = new Model();
        $m->delete(array('serviceID' => $ID), 'services');
        
        return true;
    }


}