<?php

class Domain extends Model
{
  protected static function getDomainColumns() {
    $cols = array('domainID', 'brandID', 'domainName', 'domainActive', 'parentDomainID');
    return $cols;
  }

  public function getDomainInfo($ID, $LoadBrand = FALSE) {
    $cols = self::getDomainColumns();
    $where = 'domainID = '.$ID;
    $res = self::select($cols, $where); // Dereferencing not available until php v5.4-5.5
    if ($LoadBrand)
    {
        $Brand = new Brand();
        $res['0']['brand'] = $Brand->getBrandInfo($res[0]['brandID']);
    }
    return $res[0];
  }

  public function getDomainIDs($where = NULL) {
    $cols = 'domainID';
    $order = 'domainName ASC';
    return self::select($cols, $where, $order);
  }
}