<?php

class Domain extends Model
{
  protected static function getDomainColumns() {
    $cols = array('domainID', 'domainName', 'domainActive', 'parentDomainID', 'brandID');
    return $cols;
  }

  public function getDomainInfo($domainID) {
    $cols = self::getDomainColumns();
    $where = 'domainID = '.$domainID;
    $res = self::select($cols, $where); // Dereferencing not available until php v5.4-5.5
    return $res[0];
  }

  /**
   * 
   * @param type $where
   * @return type
   * 
   * @todo Prepared statements
   */
  public function getDomainIDs($where = NULL) {
    $cols = 'domainID';
    $order = 'domainName ASC';
    return self::select($cols, $where, $order);
  }

}