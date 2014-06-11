<?php

class Usergroup extends Model
{
  protected static function getUsergroupColumns() {
    $cols = array('usergroupID', 'brandID', 'usergroupName', 'usergroupActive', 'parentUserGroupID');
    return $cols;
  }

  public function getUsergroupInfo($usergroupID, $LoadBrand = FALSE) {
    $cols = self::getUsergroupColumns();
    $where = 'usergroupID = '.$usergroupID;
    $res = self::select($cols, $where); // Dereferencing not available until php v5.4-5.5
    if ($LoadBrand)
    {
        $Brand = new Brand();
        $res['0']['brand'] = $Brand->getBrandInfo($res[0]['brandID']);
    }
    return $res[0];
  }

  public function getUsergroupIDs($where = NULL) {
    $cols = 'usergroupID';
    $order = 'usergroupID DESC';
    return self::select($cols, $where, $order);
  }
}