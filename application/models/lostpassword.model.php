<?php

class LostPassword extends Model
{

    protected static function getColumns()
    {
        $cols = array('ID', 'tempCode', 'userID', 'dateCreated');
        return $cols;
    }

    public function getWhere($where = NULL, $cols = NULL, $order = NULL, $aclWhere = NULL, $in = NULL, $loadChildren = FALSE)
    {
        if ($order == NULL)
        {
            $order = "dateCreated, userID";
        }
        return parent::getWhere($where, $cols, $order, $aclWhere, $in, $loadChildren);
    }

    public function NewEntry($userID)
    {
        Errors::debugLogger(__METHOD__,10);
        $tempCode = substr(md5(rand(0, 1000000)), 2, 12);
        $rp = array();
        $rp['tempCode'] = $tempCode;
        $rp['userID'] = $userID;
        $rp['dateCreated'] = Utility::getDateTimeUTC();
        $this->update($rp);
        return $tempCode;
    }

}