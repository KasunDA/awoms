<?php

/**
 * ACL class
 *
 * Handles authorization requests
 *
 * PHP version 5.4
 * 
 * @author    Brock Hensley <Brock@AWOMS.com>
 * 
 * @version   v00.00.0000
 * 
 * @since     v00.00.0000
 */
class ACL
{
    /**
     * Class data
     *
     * @var array $data Array holding any class data used in get/set
     */
    public $data = array();
    public static $DB;

    /**
     * __construct
     * 
     * Magic method executed on new class
     * 
     * @since v00.00.0000
     * 
     * @version v00.00.0000
     */
    public function __construct()
    {
        Errors::debugLogger(__METHOD__, 10);
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

    /**
     * Handle Access Denied
     * 
     * @param type $redirect
     * @return boolean
     */
    public static function ReturnFailedAuth($redirect = "login")
    {
        if ($redirect == "login") {
            #$_SESSION['ErrorMessage'] = "Login Required";
            #$_SESSION['ErrorRedirect'] = NULL;
            #$_SESSION['ErrorRedirect'] = "/users/login?returnURL=/" . $_SESSION['controller'] . "/" . $_SESSION['action'];
            #Session::saveSessionToDB();
            Errors::debugLogger("* ACL Returning False -> Login *");
            header('Location: /users/login?returnURL=/' . $_SESSION['controller'] . '/' . $_SESSION['action']);
            exit(0);
        }
        #$_SESSION['ErrorMessage'] = "Access Denied";
        #$_SESSION['ErrorRedirect'] = NULL;
        #$_SESSION['ErrorRedirect'] = "/users/login?access=1&returnURL=/" . $_SESSION['controller'] . "/" . $_SESSION['action'];
        #Session::saveSessionToDB();
        Errors::debugLogger("* ACL Returning False -> 403 *");
        header('Location: /users/login?access=1&returnURL=/' . $_SESSION['controller'] . '/' . $_SESSION['action']);
        exit(0);
    }

    /**
     * Checks User Access Rights
     * 
     * Check logged in users authorization for requested action
     * 
     * @param string $redirect What to return if no permissions: login | 403 | false
     * 
     * @return boolean|header(Location:...
     */
    public static function IsUserAuthorized($redirect = "login")
    {
        Errors::debugLogger(__METHOD__, 10);

        // Allow anonymous/everyone to read Home/Page/Article/Comment/Login/Logout
        if (in_array($_SESSION['action'], array('home', 'login', 'logout', 'viewall', 'view'))
                && in_array($_SESSION['controller'], array('home', 'users', 'pages', 'articles', 'comments'))) {
            return true;
        }

        Errors::debugLogger("* Checking User ACL *");
        
        if (empty($_SESSION['user'])) { return self::ReturnFailedAuth(); }
        
        
        
        // ?????????????????????????????
        // ?????????????????????????????        
        if ($_SESSION['controller'] == 'admin'
                && $_SESSION['action'] == 'home')
        {
            return true;
        }
        // ?????????????????????????????
        // ?????????????????????????????
        
        
        /* User ACL check */
        $brandID     = BRAND_ID;
        $userID      = $_SESSION['user']['userID'];
        $usergroupID = $_SESSION['user']['usergroup']['usergroupID'];
        $controller  = $_SESSION['controller'];

        // CRUD taken from controller/action
        if ($_SESSION['action'] == 'create') {
            $crud = "create";
        } elseif ($_SESSION['action'] == 'viewall' || $_SESSION['action'] == 'view' || $_SESSION['action'] = 'home') {
            $crud = "read";
        } elseif ($_SESSION['action'] == 'edit') {
            $crud = "update";
        } elseif ($_SESSION['action'] == 'delete') {
            $crud = "delete";
        }

        // Search in order of user -> brand/group -> group defaults
        // Brand's User Defaults override?
        if (self::PermissionCheckUserLevel($userID, $controller, $crud)) return true;
        
        // Group Defaults:
        if (self::PermissionCheckDefaultGroupLevel($usergroupID, $controller, $crud)) return true;

        var_dump($userID, $usergroupID, $controller, $crud, $_SESSION);
        exit;
        
        // No entry found for allow or deny so returning false (deny)
        return self::ReturnFailedAuth();
    }

    /**
     * Check ACL by User ID
     * 
     * @param type $userID
     * @param type $controller
     * @param type $crud
     * @return boolean
     */
    public static function PermissionCheckUserLevel($userID, $controller, $crud)
    {
        if (empty(self::$DB)) {
            self::$DB = new Database();
        }
        // User override?
        $sql      = "
			SELECT *
			FROM acl
			WHERE userID = :userID
              AND controller = :controller";
        $sql_data = array(':userID'     => $userID,
            ':controller' => $controller);
        $results  = self::$DB->query($sql, $sql_data);
        if (!empty($results)) {
            Errors::debugLogger(__METHOD__ . ': Found user specific ACL (' . $results[0][$crud] . ')...', 10);
            if ($results[0][$crud] == 1) {
                // Access explicitly ALLOWED:
                Errors::debugLogger(__METHOD__ . ': ACL APPROVED', 10);
                return true;
            }
            // Required access explicitly DENIED:
            Errors::debugLogger(__METHOD__ . ': ACL DENIED', 10);
            return self::ReturnFailedAuth("403");
        }
        // No entry found
        return null;
    }
    
    /**
     * Check ACL by Group ID
     * 
     * @param type $usergroupID
     * @param type $controller
     * @param type $crud
     * @return boolean
     */
    public static function PermissionCheckDefaultGroupLevel($usergroupID, $controller, $crud)
    {
        if (empty(self::$DB)) {
            self::$DB = new Database();
        }
        $sql      = "
			SELECT *
			FROM acl
			WHERE usergroupID = :usergroupID
              AND controller = :controller";
        $sql_data = array(':usergroupID'     => $usergroupID,
            ':controller' => $controller);
        $results  = self::$DB->query($sql, $sql_data);
        if (!empty($results)) {
            Errors::debugLogger(__METHOD__ . ': Found group specific ACL (' . $results[0][$crud] . ')...', 10);
            if ($results[0][$crud] == 1) {
                // Access explicitly ALLOWED:
                Errors::debugLogger(__METHOD__ . ': ACL APPROVED', 10);
                return true;
            }
            // Required access explicitly DENIED:
            Errors::debugLogger(__METHOD__ . ': ACL DENIED', 10);
            return self::ReturnFailedAuth("403");
        }
        // No entry found
        return null;
    }
}