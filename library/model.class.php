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
                $dupUp = " " . $colID . "=LAST_INSERT_ID(" . $colID . "), ";
                continue;
            }
            $cols .= $col . ", ";
            $vals .= ":" . $col . ", ";
            $dups .= $col . " = :" . $col . ", ";
            $this->sqlData[':' . $col] = $val;
        }
        $cols = substr($cols, 0, -2); // Trim last ', ' ... @TODO rtrim(', ')
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
     * @param string $where Optional
     * @param string $order Optional
     * @param string $table Optional table to specify otherwise uses $this->model
     * 
     * @return array SQL results
     */
    public function select($columns, $where = NULL, $order = NULL, $table = NULL)
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
        if ($where !== NULL)
        {
            // PDO Prepared Statements using array key => value
            if (is_array($where))
            {
                $whrs = '';
                $this->sqlData = array();        
                foreach ($where as $col => $val) {
                    // WHERE x = :x, y = :y
                    $whrs .= $col . " = :" . $col . ", ";
                    // ':x' = 'x123', ':y' = 'y321'
                    $this->sqlData[':' . $col] = $val;
                }
                $where = rtrim($whrs, ", ");
            }
        }
        
        // Query
        $this->sql = "
            SELECT " . $cols . "
            FROM " . $table;
        
        if (!empty($where)) {
            $this->sql .= "
            WHERE " . $where;
        }
        
        if (!empty($order)) {
            $this->sql .= "
            ORDER BY " . $order;
        }
        
        if (!empty($this->sqlData))
        {
            return $this->makeRawDbTextSafeForHtmlDisplay($this->query($this->sql, $this->sqlData));
        }
        return $this->makeRawDbTextSafeForHtmlDisplay($this->query($this->sql));
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
        $vals          = '';
        $this->sqlData = array();
        foreach ($data as $col => $val) {
            if (empty($vals))
            {
                $vals = $col . " = :" . $col;
            }
            else
            {
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
        
        if ($limit != FALSE)
        {
            if ($limit == TRUE) {$limit = 1;}
            $this->sql .= " LIMIT ".$limit;
        }

        return $this->query($this->sql, $this->sqlData);
    }

    public function getAll()
    {
        $cols = "*";
        $where = NULL;
        $order = NULL;
        $table = NULL;
        // ACL: Non-admins restricted list to active brand for these controllers (they have brandID field)
        if (in_array($this->table, array('brands', 'domains', 'usergroups', 'menus', 'pages', 'articles')))
        {
            if ($_SESSION['user']['usergroup']['usergroupName'] != "Administrators") {
                $where = array('brandID', $_SESSION['brandID']);
            }
        } else {
            trigger_error("NotYetImplemented", E_USER_ERROR);
            exit(0);
        }

        $all = self::select($cols, $where, $order, $table);
        return $all;
    }
    
    /**
     * Ensures things like "Goin' Postal" are displayed as "Goin&#39 Postal"
     * 
     * @uses htmlentities
     * 
     * @param array|string $raw
     * @return array|string
     */
    public function makeRawDbTextSafeForHtmlDisplay($raw)
    {
        array_walk_recursive($raw, function (&$value) {
            $value = htmlentities($value, ENT_QUOTES);
        });
        return $raw;
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