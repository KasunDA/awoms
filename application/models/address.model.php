<?php

class Address extends Model
{
    protected static function getColumns()
    {
        $cols = array('addressID', 'firstName', 'middleName', 'lastName', 'phone', 'email', 'line1', 'line2', 'line3', 'city',
            'zipPostcode', 'stateProvinceCounty', 'country', 'addressNotes');
        return $cols;
    }

    public function getWhere($where = NULL, $cols = NULL, $order = NULL, $aclWhere = NULL, $in = NULL, $loadChildren = FALSE)
    {
        if ($order == NULL) {
            $order = "stateProvinceCounty, line1";
        }
        return parent::getWhere($where, $cols, $order, $aclWhere, $in, $loadChildren);
    }

}