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
  
  /**
   * Validates user login from db, returning false or user info
   * 
   * @param type $username
   * @param type $passphrase
   * @return array|boolean Array - valid login | False - invalid login
   */
  public function ValidateLogin($username, $passphrase)
  {
      Errors::debugLogger(__METHOD__);
      
      // Get brands usergroups
      $cols = "usergroupID";
      /**
        * The hard coded 1 here allow for shared usergroups amongst all brands (groups created under main brand id 1 are shared)
        * If the brand has its own custom groups it will look for them as well
        */
      $sharedBrandID = 1;
      $where = "brandID IN (".$sharedBrandID.", ".BRAND_ID.")
          AND usergroupActive = 1";
      $thisBrandsGroups = self::select($cols, $where, NULL, 'usergroups');
      if (empty($thisBrandsGroups)) return false;
      

      $w = "usergroupID IN (";
      foreach ($thisBrandsGroups as $group)
      {
          $w .= $group['usergroupID'].",";
      }
      $w = rtrim($w, ",");
      $w .= ")";
      
      // Valid login?
      $cols = "userID";
      $where   = $w."
            AND userActive = 1
            AND userName = '".$username."'
            AND passphrase = '".$passphrase."'";
      $r = self::select($cols, $where);

      if (empty($r[0]['userID']))
      {
          return false;
      }
      
      return self::getUserInfo($r[0]['userID'], TRUE);
  }

}