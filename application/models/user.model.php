<?php

class User extends Model
{
  protected static function getUserColumns() {
    $cols = array('userID', 'userName', 'userActive');
    return $cols;
  }

  public function getUserInfo($userID) {
    $cols = self::getUserColumns();
    $where = 'userID = '.$userID;
    $res = self::select($cols, $where); // Dereferencing not available until php v5.4-5.5
    return $res[0];
  }

  public function getUserIDs($where = NULL) {
    $cols = 'userID';
    $order = 'userID DESC';
    return self::select($cols, $where, $order);
  }

}