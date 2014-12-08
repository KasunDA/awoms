<?php
namespace killerCart;

/**
 * User class
 *
 * User management methods
 */
class User
{
    /**
     * Database connection
     * Database query sql
     *
     * @var PDO $DB
     * @var string $sql
     */
    protected $DB, $sql, $sqlData;

    /**
     * Class data
     *
     * @var array $data
     */
    protected $data = array();

    /**
     * Main magic methods
     */
    public function __construct()
    {
        \Errors::debugLogger(__METHOD__, 5);
        $this->DB = new \Database();
    }

    public function __destruct()
    {
        unset($this->DB, $this->sql);
    }

    public function __set($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function __get($key)
    {
        if ($this->__isset($key)) {
            return $this->data[$key];
        }
        return false;
    }

    public function __isset($key)
    {
        if (array_key_exists($key, $this->data)) {
            return true;
        } else {
            return false;
        }
    }





    

    // @todo save user group on edit not saving
    /**
     * setUserGroup
     * 
     * Sets which group the user belongs to
     * 
     * @param int $cartID Cart ID
     * @param int $userID User ID
     * @param int $groupID Group ID to make active
     * 
     * @return boolean
     */
    public function setUserGroup($cartID, $userID, $groupID)
    {
        \Errors::debugLogger(__METHOD__, 5);
        $this->sql     = "
            INSERT INTO cartGroupUsers
            (cartID, groupID, userID)
            VALUES
            (:cartID, :groupID, :userID)
            ON DUPLICATE KEY UPDATE cartID = :cartID,
                groupID = :groupID,
                userID = :userID";
        $this->sqlData = array(':cartID' => $cartID, ':userID'  => $userID, ':groupID' => $groupID);
        $res           = $this->DB->query($this->sql, $this->sqlData);
        if (!isset($res)) {
            trigger_error("Error #U008: Invalid results.", E_USER_ERROR);
            return false;
        }
        return true;
    }

    /**
     * getCartGroups
     * 
     * Gets all groups
     * 
     * @return boolean|array
     */
    public function getGroups()
    {
        \Errors::debugLogger(__METHOD__, 5);
        $this->sql = "
            SELECT groupID, groupName, groupDescription
            FROM cartUserGroups";
        $res       = $this->DB->query($this->sql);
        if (!isset($res)) {
            trigger_error('Unexpected results.', E_USER_ERROR);
            return false;
        }
        return $res;
    }

    /**
     * getUserCount
     *
     * Gets count of users of selected type and optionally of selected cart ID
     *
     * @param string $type Type of User: All, Active, Inactive
     * @param int $cartID (Optional) Cart ID to include
     * @return int Count of users
     */
    public function getUserCount($type = false, $cartID = false)
    {
        // Validate parameters
        if (empty($type) || func_num_args($type) > 2 || !is_string($type)):
            \Errors::debugLogger(__METHOD__, 5);
            trigger_error('Error #U005: Invalid parameters.', E_USER_ERROR);
            return false;
        endif;
        // Type
        if (strtolower($type) == 'all'):
            $whereSql = 'userID IS NOT NULL';
        elseif (strtolower($type) == 'active'):
            $whereSql = "userActive='1'";
        elseif (strtolower($type) == 'inactive'):
            $whereSql = "userActive='0'";
        else:
            \Errors::debugLogger(__METHOD__, 5);
            trigger_error('Error #U006: Invalid parameters.', E_USER_ERROR);
            return false;
        endif;
        /* /* Cart
          if (!empty($cartID)):
          $whereSql .= ' AND cartID'
          endif;
         * 
         */

        // SQL
        $this->sql  = "
			SELECT COUNT(`userID`) as Total
			FROM `cartUsers`
			WHERE " . $whereSql;
        $return_arr = $this->DB->query($this->sql);
        if (!empty($return_arr)) {
            return $return_arr[0]['Total'];
        } else {
            return false;
        }
    }

    /*
     * getUserIDs
     */
    public function getUserIDs($user_type = false)
    {
        \Errors::debugLogger(__METHOD__, 5);
        if (empty($user_type) || func_num_args($user_type) > 1 || is_array($user_type)) {
            trigger_error("Error #U007: Invalid parameters.", E_USER_ERROR);
            return false;
        }
        if (strtolower($user_type) == "all") {
            $whereSql = "userID IS NOT NULL";
        } elseif (strtolower($user_type) == "active") {
            $whereSql = "userActive='1'";
        } elseif (strtolower($user_type) == "inactive") {
            $whereSql = "userActive='0'";
        } else {
            trigger_error("Error #U008: Invalid parameters.", E_USER_ERROR);
            return false;
        }
        $this->sql  = "
			SELECT `userID`
			FROM `cartUsers`
			WHERE " . $whereSql;
        $return_arr = $this->DB->query($this->sql);
        if (!empty($return_arr)) {
            return $return_arr;
        } else {
            return false;
        }
    }

    /**
     * getUserInfo
     * 
     * @param int $userID
     * 
     * @return boolean|array
     */
    public function getUserInfo($userID)
    {
        \Errors::debugLogger(__METHOD__, 5);
        $this->sql     = "
			SELECT
                su.userID, su.username, su.email, su.userActive, su.userNotes,
                sgu.groupID, sgu.cartID,
                sug.groupName,
                s.cartName, s.cartTheme
			FROM
                cartUsers AS su
                    INNER JOIN cartGroupUsers AS sgu
                        ON su.userID = sgu.userID
                    INNER JOIN cartUserGroups AS sug
                        ON sgu.groupID = sug.groupID
                    INNER JOIN carts AS s
                        ON sgu.cartID = s.cartID
			WHERE
                su.userID = :userID
            LIMIT 1";
        $this->sqlData = array(':userID' => $userID);
        $res           = $this->DB->query($this->sql, $this->sqlData);
        if (!isset($res) || empty($res[0])) {
            \Errors::debugLogger(__METHOD__ . ':  UserID: ' . $userID);
            \Errors::debugLogger($res);
            trigger_error("Error #U004: Invalid results.", E_USER_ERROR);
            return false;
        }
        return $res[0];
    }

    /*
      public function getUserStatus() {
      if (empty($this->id)) { trigger_error("Error #139: Missing user id (".$this->id.")", E_USER_ERROR); return false; }
      $this->sql = "
      SELECT `active`
      FROM `" . DBNAME . "`.`users`
      WHERE `userID` = '".$this->id."'";
      $return_arr = $this->DB->query($this->sql);

      if (isset($return_arr)) {
      return $return_arr[0]['active'];
      } else {
      trigger_error("Error #148: ", E_USER_ERROR);
      return false;
      }
      }
     */


    /*
      public function getUserName() {
      if (empty($this->id)) { trigger_error("Error #287: Missing user id (".$this->id.")", E_USER_ERROR); return false; }
      $this->sql = "
      SELECT `settingID`, `name`, `type`, `value`
      FROM `" . DBNAME . "`.`user_settings`
      WHERE `userID` = '".$this->id."'
      AND `name` = BINARY 'name'
      ";
      $return_arr = $this->DB->query($this->sql);
      if (!empty($return_arr)) {
      $this->name = $return_arr[0]['value'];
      return true;
      } else {
      trigger_error("Error #302: ", E_USER_ERROR);
      return false;
      }
      }
     */
}
?>