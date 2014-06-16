<?php

/**
 * Model class
 * 
 * CRUD database operations
 * 
 * PHP version 5.4
 * 
 * @author    Brock Hensley <Brock@AWOMS.com>
 * 
 * @version   v00.00.0000
 * 
 * @since     v00.00.0000
 * 
 * @example $db = Database::getDB();
 */
class Model extends Database
{
    /**
     * Model data
     * 
     * @var string $model Model
     * @var string $table Table
     */
    protected $model, $table;

    /**
     * __construct
     * 
     * Magic method executed on new class
     * 
     * @since v00.00.0000
     * 
     * @version v00.00.0000
     * 
     * @uses Database()
     * @uses routeRequest()
     * 
     * @param string $model Model name
     * @param string $table Table name
     */
    public function __construct()
    {
        if (empty($this->db)) {
            parent::connect();
        }
        $this->model = get_class($this);
        $this->table = strtolower($this->model) . "s";
    }

    /**
     * __destruct
     * 
     * Magic method executed on class end
     * 
     * @since v00.00.0000
     * 
     * @version v00.00.0000
     */
    #public function __destruct() {
    // Triggers Database::__destruct [?]
    #}
    /**
     * update
     * 
     * Used to execute INSERT or UPDATE queries on a SINGLE table
     * 
     * @since v00.00.0000
     * 
     * @version v00.00.0000
     * 
     * @param array $data Data col=>data
     * @param string $table Optional table to specify otherwise uses $this->model
     * 
     * @return int Auto inc ID
     */
    function update($data, $table = NULL)
    {
        $cols          = '';
        $vals          = '';
        $dups          = '';
        $this->sqlData = array();
        $colID         = '';
        foreach ($data as $col => $val) {
            if ($colID == '' && preg_match('/ID/', $col) && preg_match('/DEFAULT/', $val)) {
                $colID = $col;
                $dupUp = " `" . $colID . "`=LAST_INSERT_ID(`" . $colID . "`), ";
                continue;
            }
            $cols .= "`" . $col . "`, ";
            $vals .= ":_" . $col . ", "; // _ underscore allows for reserved words/columns like 'create', 'read'
            $dups .= "`" . $col . "`=:_" . $col . ", ";
            $this->sqlData[':_' . $col] = $val;
        }
        $cols = substr($cols, 0, -2); // Trim last ', '
        $vals = substr($vals, 0, -2); // Trim last ', '
        $dups = substr($dups, 0, -2); // Trim last ', '

        if ($table === NULL) {
            $table = $this->table;
        }

        $this->sql = "
      INSERT INTO " . $table . "
        (" . $cols . ")
      VALUES
        (" . $vals . ")
      ON DUPLICATE KEY UPDATE ";

        if (!empty($dupUp)) {
            $this->sql .= $dupUp;
        }

        $this->sql .= $dups;
        
        return $this->query($this->sql, $this->sqlData);
    }

    /**
     * select
     * 
     * Used to execute SELECT queries on a SINGLE table
     * 
     * @since v00.00.0000
     * 
     * @version v00.00.0000
     * 
     * @param array $columns
     * @param string|array $where Optional col => val
     * @param string $order Optional
     * @param string $table Optional table to specify otherwise uses $this->model
     * @param array $in Optional IN array - use with $where string: $where='col', $in=array('this','or','that')
     * 
     * @return array SQL results
     */
    public function select($columns, $where = NULL, $order = NULL, $table = NULL, $in = NULL)
    {
        // Columns
        if (is_array($columns)) {
            $cols = "`" . implode("`,`", $columns) . "`";
        } else {
            $cols = $columns;
        }

        // Table
        if ($table === NULL) {
            $table = $this->table;
        }

        // Where
        if ($where !== NULL) {
            // PDO Prepared Statements using array key => value
            if (is_array($where)) {
                
                $whrs          = '';
                $this->sqlData = array();
                foreach ($where as $col => $val) {
                    
                    // If using IN, and last col's val is empty, join that to where for IN appended next
                    if (empty($val) && !empty($in) && is_array($in))
                    {
                        $in_col = $col;
                        continue;
                    }
                    
                    // WHERE x = :x, y = :y
                    $whrs .= $col . " = :" . $col . " AND ";
                    // ':x' = 'x123', ':y' = 'y321'
                    $this->sqlData[':' . $col] = $val;
                }
                $where = substr($whrs, 0, -4); // Trim last ' AND '
            }
        }

        // Query
        $this->sql = "
            SELECT " . $cols . "
            FROM " . $table;

        // WHERE
        if (!empty($where)) {
            $this->sql .= "
            WHERE " . $where;
            
            // IN ?
            if (!empty($in) && is_array($in))
            {
                $_in = "(";
                foreach ($in as $n)
                {
                    $_in .= $n.",";
                }
                $_in = substr($_in, 0, -1); // Trim last ','
                $_in .= ")";
                
                // No empty column; normal where string
                if (empty($in_col))
                {
                    $this->sql .= "
                    IN ".$_in;
                } else {
                    // empty column was passed in where array
                    $this->sql .= " AND ".$in_col." IN ".$_in;
                }
            }
        }

        if (!empty($order)) {
            $this->sql .= "
            ORDER BY " . $order;
        }
        
        if (!empty($this->sqlData)) {
            return Utility::makeRawDbTextSafeForHtmlDisplay($this->query($this->sql, $this->sqlData));
        }
        return Utility::makeRawDbTextSafeForHtmlDisplay($this->query($this->sql));
    }

    /**
     * delete
     * 
     * Used to execute DELETE queries on a SINGLE table
     * 
     * @since v00.00.0000
     * 
     * @version v00.00.0000
     * 
     * @param array $data Data col=>data
     * @param string $table Optional table to specify otherwise uses $this->model
     * @param int $limit Optional limit clause
     * 
     * @return boolean
     */
    function delete($data, $table = NULL, $limit = FALSE)
    {
        if (empty($data)) return false;
        
        $vals          = '';
        $this->sqlData = array();
        foreach ($data as $col => $val) {
            if (empty($vals)) {
                $vals = $col . " = :" . $col;
            } else {
                $vals .= " AND " . $col . " = :" . $col;
            }
            $this->sqlData[':' . $col] = $val;
        }

        if ($table === NULL) {
            $table = $this->table;
        }

        $this->sql = "
            DELETE FROM " . $table . "
            WHERE " . $vals . " ";

        if ($limit != FALSE) {
            if ($limit == TRUE) {
                $limit = 1;
            }
            $this->sql .= " LIMIT " . $limit;
        }
        $res = $this->query($this->sql, $this->sqlData);
        if (!empty($res)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * ACL: Non-admins restricted list to active brand for these controllers (they have brandID field)
     * 
     * @return array|null
     */
    private function aclWhere()
    {
        $res = array();
        $res['where'] = NULL;
        $res['in'] = NULL;
        
        // No session if we are looking up domain so allow
        // Also allow rewrite lookups
        if (empty($_SESSION) || $this->table == "rewritemappings")
        {
            return $res;
        }

        if (empty($_SESSION['user']))
        {
            // Use brandID from active domain/session
            $brandID = $_SESSION['brandID'];
        }
        else
        {
            // Use brandID from usergroup (can be different from live brandID)
            $brandID = $_SESSION['user']['usergroup']['brandID'];
        }

        // Tables that do NOT have brandID column but still need to be restricted by the associated brand
        // users -> usergroup -> brand
        // menulinks -> menu -> brand
        // comments|body -> comment|article -> brand
        if (in_array($this->table, array('users', 'menulinks', 'comments', 'bodycontents')))
        {
            if ($this->table == 'users')
            {
                // users -> usergroup -> brand (brand has many usergroups, uses in:)
                // get users where usergroupID in (all usergroupIDs belonging to brandID)
                $res['where'] = 'usergroupID';
                $Usergroup = new Usergroup();
                $ins = $Usergroup->getWhere(array('brandID' => $brandID));
                foreach ($ins as $in)
                {
                    $res['in'] .= $in['usergroupID'].",";
                }
                $res['in'] = substr($res['in'], 0, -1);
            }
            elseif ($this->table == 'menulinks')
            {
                // menulinks -> menu -> brand (brand has many menus, uses in:)
                // get menulinks where menuID in (all menuIDs belonging to brandID)
                $res['where'] = 'menuID';
                $Menu = new Menu();
                $ins = $Menu->getWhere(array('brandID' => $brandID));
                foreach ($ins as $in)
                {
                    $res['in'] .= $in['menuID'].",";
                }
                $res['in'] = substr($res['in'], 0, -1);
            }
            elseif ($this->table == 'comments'
                    || $this->table == 'bodycontents')
            {
                // @TODO:

                // commentID/bodyContentID => parentItemTypEID => parentItemID => articleID/commentID => brandID
                
                // comments/body -> parentitem (typeid) -> brand (brand has many parent items, uses in:)

                // get comments where parentitem(of parentitemTypeID) in (all parentItemIDs (oftypeID) belonging to brandID)
                
                // need to do article, page, comment...
            }

            return $res;
        }

        // Non-Global-Admins (BrandID=1, Group=Admin) == limited by brand
        if (empty($_SESSION['user'])
                || $_SESSION['user']['usergroup']['usergroupName'] != "Administrators"
                || $_SESSION['user']['usergroup']['brandID'] != 1)
        {
            $res['where'] = array('brandID' => $brandID);
        }
        
        return $res;
    }

    /**
     * Gets all items
     * 
     * @uses aclWhere
     * 
     * @return array
     */
    public function getAll()
    {
        $cols     = "*";
        $aclWhere = self::aclWhere();
        
        if (!empty($aclWhere['where'])) { $where = $aclWhere['where']; } else { $where = NULL; }
        if (!empty($aclWhere['in'])) { $in = $aclWhere['in']; } else { $in = NULL; }
        $order    = NULL;
        $table    = NULL;
        
        if ($in != NULL && !is_array($in))
        {
            $in = explode(',', $in);
        }
        
        $all      = self::select($cols, $where, $order, $table, $in);
        
        // Load Extended Item Info
        $newItems = array();
        foreach ($all as $item)
        {
            /* Controller specific: */
            $model = $this->model;
            $item  = $model::LoadExtendedItem($item); //."sController";
            $newItems[] = $item;
        }
        
        return $newItems;
    }

    /**
     * Returns single item of results or false if none found
     * 
     * @uses getWhere
     * 
     * @param array $where
     * @param array $in
     * @return boolean|array
     */
    public function getSingle($where, $in = NULL)
    {
        $res = self::getWhere($where, $in);
        if (!empty($res))
        {
            return $res[0];
        }
        return false;
    }
    
    /**
     * Gets items matching where clause
     * 
     * @uses aclWhere
     * 
     * @param array $where = array('col' => 'findMe');
     * 
     * @return array
     */
    public function getWhere($where, $in = NULL)
    {
        $cols     = "*";

        // Every query is restricted by BrandID here
        $aclWhere = self::aclWhere();
        
        if (!empty($aclWhere['where'])) { $_where = $aclWhere['where']; } else { $_where = NULL; }
        if (!empty($aclWhere['in'])) { $_in = $aclWhere['in']; } else { $_in = NULL; }
        if (is_array($_where)) {
            $where = array_replace_recursive($where, $_where);
        }
        if (is_array($_in)) {
            $in = array_replace_recursive($in, $_in);
        }
        
        // Can append/merge: $aclWhere['col'] = 'val' (--unless using $in--);
        $order = NULL;
        $table = NULL;
        $all   = self::select($cols, $where, $order, $table, $in);

        return $all;
    }

    /**
     * Load additional model specific info when getWhere is called
     * 
     * @param type $item
     */
    public static function LoadExtendedItem($item)
    {
        // Look in specific Model
        return $item;
    }

    /**
     * saveBodyContents
     * 
     * Used to save body contents of x parent item (used by multiple models)
     * 
     * @since v00.00.0000
     * 
     * @version v00.00.0000
     * 
     * @param type $parentItemID
     * @param type $parentItemTypeID
     * @param type $bodyContentText
     * @param type $userID
     * 
     * @return int Body ID
     */
    public function saveBodyContents($parentItemID, $parentItemTypeID, $bodyContentText, $userID)
    {

        $table                   = 'bodyContents';
        $bodyContentActive       = 1;
        $bodyContentDateModified = Utility::getDateTimeUTC();

        // Construct sql data
        $cols  = array('bodyContentID', 'parentItemID', 'parentItemTypeID', 'bodyContentActive', 'bodyContentDateModified', 'bodyContentText',
            'userID');
        $data  = array('DEFAULT', $parentItemID, $parentItemTypeID, $bodyContentActive, $bodyContentDateModified, $bodyContentText, $userID);
        $final = array();
        $i     = 0;
        foreach ($cols as $col) {
            $final[$col] = $data[$i];
            $i++;
        }

        return self::update($final, $table);
    }

    /**
     * getBodyContents
     * 
     * Used to get body contents of x parent item (used by multiple models)
     * 
     * @since v00.00.0000
     * 
     * @version v00.00.0000
     * 
     * @param int $parentItemID
     * @param int $parentItemTypeID
     * @param int $bodyContentID Optional where clause
     * @param int $bodyContentActive Optional
     * 
     * @return array SQL results
     */
    public function getBodyContents($parentItemID, $parentItemTypeID, $bodyContentID = NULL, $bodyContentActive = NULL)
    {
        $this->sql     = "
      SELECT bodyContentID, parentItemID, parentItemTypeID, bodyContentActive, bodyContentDateModified, bodyContentText, userID
      FROM bodyContents
      WHERE parentItemID = :parentItemID
        AND parentItemTypeID = :parentItemTypeID";
        $this->sqlData = array(':parentItemID'     => $parentItemID,
            ':parentItemTypeID' => $parentItemTypeID);

        // bodyContentID
        if ($bodyContentID !== NULL) {
            $this->sql .= " AND bodyContentID = :bodyContentID";
            $this->sqlData[':bodyContentID'] = $bodyContentID;
        }

        // bodyContentActive
        if ($bodyContentActive !== NULL) {
            $this->sql .= " AND bodyContentActive = :bodyContentActive";
            $this->sqlData[':bodyContentActive'] = $bodyContentActive;
        }

        return $this->query($this->sql, $this->sqlData);
    }

    /**
     * setBodyContentActive
     * 
     * Sets all other body contents to inactive to make the chosen id active
     * 
     * @param int $parentItemID
     * @param int $parentItemTypeID
     * @param int $bodyContentID
     * 
     * @return type
     */
    public function setBodyContentActive($parentItemID, $parentItemTypeID, $bodyContentID)
    {
        $this->sql     = "
      UPDATE bodyContents
      SET bodyContentActive = :bodyContentActive
      WHERE parentItemID = :parentItemID
        AND parentItemTypeID = :parentItemTypeID
        AND bodyContentID != :bodyContentID";
        $this->sqlData = array(':parentItemID'      => $parentItemID,
            ':parentItemTypeID'  => $parentItemTypeID,
            ':bodyContentActive' => 0,
            ':bodyContentID'     => $bodyContentID);
        return $this->query($this->sql, $this->sqlData);
    }

}