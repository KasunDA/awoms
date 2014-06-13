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
     * @param string $redirect
     * @return boolean
     */
    public static function ReturnFailedAuth($redirect)
    {
        #Errors::debugLogger(__METHOD__, 90);
        if ($redirect == "login" && empty($_SESSION['user'])) {
            #$_SESSION['ErrorMessage'] = "Login Required";
            #$_SESSION['ErrorRedirect'] = NULL;
            #$_SESSION['ErrorRedirect'] = "/users/login?returnURL=/" . $_SESSION['controller'] . "/" . $_SESSION['action'];
            #Session::saveSessionToDB();
            Errors::debugLogger("* ACL Returning False -> Login *");
            if ($_SESSION['controller'] == 'admin' && $_SESSION['action'] == 'home') {
                $returnURL = "owners";
            } else {
                $returnURL = $_SESSION['controller'] . '/' . $_SESSION['action'];
            }
            header('Location: /users/login?returnURL=/' . $returnURL);
            exit(0);
        } elseif ($redirect == "403" || ($redirect == "login" && !empty($_SESSION['user']))) {
            #$_SESSION['ErrorMessage'] = "Access Denied";
            #$_SESSION['ErrorRedirect'] = NULL;
            #$_SESSION['ErrorRedirect'] = "/users/login?access=1&returnURL=/" . $_SESSION['controller'] . "/" . $_SESSION['action'];
            #Session::saveSessionToDB();
            Errors::debugLogger("* ACL Returning False -> 403 *");
            header('Location: /users/login?access=1&returnURL=/' . $_SESSION['controller'] . '/' . $_SESSION['action']);
            exit(0);
        } else {
            Errors::debugLogger("* ACL Returning False -> False *");
            return false;
        }
    }

    /**
     * Checks User Access Rights
     * 
     * Check logged in users authorization for requested action
     * 
     * @param string $redirect What to return if no permissions: login | 403 | false
     * 
     * @uses ReturnFailedAuth
     * 
     * @return boolean|header(Location:...
     */
    public static function IsUserAuthorized($controller, $action, $redirect = NULL)
    {
        Errors::debugLogger(__METHOD__ . ': ' . $controller . '/' . $action, 90);
        // Allowed anonymous access:
        if (
        // Menus
                ($controller == "menus" && $action == "admin") ||
                // Home
                ($controller == "home" && $action == "home") ||
                // Users login
                ($controller == "users" && in_array($action, array('login', 'logout'))) ||
                // Anonymous Page/Article/Comment/
                (empty($_SESSION['user']) && in_array($controller, array('pages', 'articles', 'comments')) && in_array($action,
                                                                                                                       array('view', 'readall')))
        ) {
            return true;
        }

        #Errors::debugLogger("* Checking for Non-Anonymous User ACL *");
        if (empty($_SESSION['user'])) {
            return self::ReturnFailedAuth($redirect);
        }

        // User is logged in, allow access to /admin/home (/owners) - template does rest
        if ($controller == 'admin' && $action == 'home') {
            return true;
        }

        /* User ACL check */
        $brandID     = BRAND_ID;
        $userID      = $_SESSION['user']['userID'];
        $usergroupID = $_SESSION['user']['usergroup']['usergroupID'];

        // CRUD taken from controller/action
        if ($action == 'create') {
            $crud = "create";
        } elseif ($action == 'readall' || $action == 'view' || $action = 'home') {
            $crud = "read";
        } elseif ($action == 'update') {
            $crud = "update";
        } elseif ($action == 'delete') {
            $crud = "delete";
        }

        // Search in order of user -> brand/group -> group defaults
        // Brand's User Defaults override?
        $test = self::PermissionCheckUserLevel($userID, $controller, $crud);
        if ($test === TRUE) {
            return true;
        } elseif ($test === FALSE) {
            return self::ReturnFailedAuth($redirect);
        }

        // Group Defaults:
        $test = self::PermissionCheckDefaultGroupLevel($usergroupID, $controller, $crud);
        if ($test === TRUE) {
            return true;
        } elseif ($test === FALSE) {
            return self::ReturnFailedAuth($redirect);
        }

        // No entry found for allow or deny so returning false (deny)
        return self::ReturnFailedAuth($redirect);
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
            Errors::debugLogger(__METHOD__ . ': Found user specific ACL (' . $results[0][$crud] . ')...', 90);
            if ($results[0][$crud] == 1) {
                // Access explicitly ALLOWED:
                Errors::debugLogger(__METHOD__ . ': ACL APPROVED', 90);
                return true;
            }
            // Required access explicitly DENIED:
            Errors::debugLogger(__METHOD__ . ': ACL DENIED', 90);
            return false;
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
        $sql_data = array(':usergroupID' => $usergroupID,
            ':controller'  => $controller);
        $results  = self::$DB->query($sql, $sql_data);
        if (!empty($results)) {
            Errors::debugLogger(__METHOD__ . ': Found group specific ACL (' . $results[0][$crud] . ')...', 90);
            if ($results[0][$crud] == 1) {
                // Access explicitly ALLOWED:
                Errors::debugLogger(__METHOD__ . ': ACL APPROVED', 90);
                return true;
            }
            // Required access explicitly DENIED:
            Errors::debugLogger(__METHOD__ . ': ACL DENIED', 90);
            return false;
        }
        // No entry found
        return null;
    }

}