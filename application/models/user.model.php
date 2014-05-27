<?php

class User extends Model
{
  protected static function getUserColumns() {
    $cols = array('userID', 'usergroupID', 'userName', 'userActive', 'userEmail', 'passphrase');
    return $cols;
  }

  public function getUserInfo($userID, $LoadUsergroup = FALSE) {
    $cols = self::getUserColumns();
    $where = 'userID = '.$userID;
    $res = self::select($cols, $where); // Dereferencing not available until php v5.4-5.5
    if ($LoadUsergroup)
    {
        $Usergroup = new Usergroup();
        $res['0']['usergroup'] = $Usergroup->getUsergroupInfo($res[0]['usergroupID']);
    }
    return $res[0];
  }

  public function getUserIDs($where = NULL) {
    $cols = 'userID';
    $order = 'userID DESC';
    return self::select($cols, $where, $order);
  }

}