<?php

class Usergroup extends Model
{
  protected static function getUsergroupColumns() {
    $cols = array('usergroupID', 'usergroupName', 'usergroupActive');
    return $cols;
  }

  public function getUsergroupInfo($usergroupID) {
    $cols = self::getUsergroupColumns();
    $where = 'usergroupID = '.$usergroupID;
    $res = self::select($cols, $where); // Dereferencing not available until php v5.4-5.5
    return $res[0];
  }

  public function getUsergroupIDs($where = NULL) {
    $cols = 'usergroupID';
    $order = 'usergroupID DESC';
    return self::select($cols, $where, $order);
  }

}