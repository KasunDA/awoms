<?php

/**
 * Database class
 * 
 * Will re-use database connection once established
 * 
 * @example $db = Database::getDB();
 */
class Database
{

  /**
   * Database data
   * 
   * @var PDO $db Database connection // @TODO should this be static for singleton?
   * @var string $sql Database query sql
   * @var array $sqlData Database query data
   */
  protected $db, $sql, $sqlData;

  /**
   * Constructor
   * 
   * @uses connect()
   */
  public function __construct() {
    self::connect();
  }

  /**
   * Destructor
   */
  public function __destruct() {
    $this->db = NULL;
  }

  /**
   * connect
   * 
   * Returns new or existing database connection
   * 
   * @access public
   * 
   * @return PDO $db
   */
  public function connect() {
    if (empty($this->db)) {
      try {
        $dsn      = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
        $this->db = new PDO($dsn, DB_USER, DB_PASSWORD, array(
          PDO::ATTR_PERSISTENT       => true,
          PDO::ATTR_ERRMODE          => PDO::ERRMODE_EXCEPTION,
          PDO::ATTR_EMULATE_PREPARES => true));
      } catch (PDOException $e) {
          trigger_error("Error #D001: Database issue. " . $e->getMessage(), E_USER_ERROR);
      }
    }
    return $this->db;
  }

  /**
   * query
   * 
   * Runs a prepared statement query using the current connection to the database
   * 
   * @param string $query
   * @param array $args An array of prepared values e.g. array(":name" => "foo")
   * 
   * @throws PDOException
   * 
   * @return array|int|boolean Containing all the remaining rows in the result set.
   */
  public function query($query, $args = false) {

    $query  = str_replace(array("\r\n", "\r", "\t", "    "), " ", $query);
    
    // Is this a SELECT/INSERT/UPDATE/REPLACE
    $tokens = array_map('trim', explode(" ", trim($query)));
    
    //
    // Query
    //
    try {
      
      // Prepare results
      $results = false;
      
      // Allow for rollback if query fails
      $this->db->beginTransaction();
      
      // Prepared query statements
      $sth     = $this->db->prepare($query);
      
      // Execute prepared statement, with or without arguments
      if (empty($args)) {
    
        // Plain query execute
        $sth->execute();
        
      } else {
        
        // Prepared statment with arguments
        
        // Are any arguments an array?
        $multiple = false;
        foreach ($args as $arg) {
          if (!is_array($arg)) {
            continue;
          }
          $multiple = true;
          break;
        }
        
        if ($multiple) {
          
          // Has arrays in args
          foreach ($args as $arg) {
            foreach ($arg as $k => $v) {
              // Check items for "NULL" string to NULL conversion
              if ($v === "NULL") {
                $arg[$k] = NULL;
              }
            }
            
            // Execute statement for each argument list
            $sth->execute($arg);
          }
          
        } else {
          
          // No arrays in args
          foreach ($args as $a => $arg) {
            // Check items for "NULL" string to NULL conversion
            if ($arg === "NULL") {
              $args[$a] = NULL;
            }
          }
          
          // Execute statement with argument list
          $sth->execute($args);
        }
      }
      
      //
      // Results
      // 

      // SELECT: Return array of data or false if 0 rows
      if ($tokens[0] == "SELECT") {
        $sth->setFetchMode(PDO::FETCH_ASSOC);
        $results = $sth->fetchAll();
      }
      // INSERT/UPDATE/REPLACE: Return number of affected rows / array of affected ids ?
      elseif ($tokens[0] == "INSERT" || $tokens[0] == "UPDATE" || $tokens[0] == "REPLACE") { // <-- UPDATE/REPLACE supposed to be here? -- insert on duplicate key update = ?
        $results = $this->db->lastInsertId();
      }
      // Else: Return number of affected rows
      else {
        $results = $sth->rowCount();   //->lastQueryRowCount();
      }
      
      //
      // Attempt to commit changes, triggers exception if fails
      //
      $this->db->commit();

    } catch (PDOException $e) {
      // Rollback changes on failure
      $this->db->rollBack();
      trigger_error("Error #D002: Query issue. Message: " . $e->getMessage() . "<hr />Query: " . $query, E_USER_ERROR);
      return false;
    }
    // Return results
    return $results;
  }

}