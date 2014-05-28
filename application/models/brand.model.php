<?php

class Brand extends Model
{
  protected static function getBrandColumns() {
    $cols = array('brandID', 'brandName', 'brandActive', 'brandLabel', 'activeTheme');
    return $cols;
  }

  public function getBrandInfo($brandID) {
    $cols = self::getBrandColumns();
    $where = 'brandID = '.$brandID;
    $res = self::select($cols, $where); // Dereferencing not available until php v5.4-5.5
    return $res[0];
  }

  public function getBrandIDs($where = NULL) {
    $cols = 'brandID';
    $order = 'brandName ASC';
    return self::select($cols, $where, $order);
  }

}