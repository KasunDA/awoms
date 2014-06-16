<?php

class ACL extends Model
{
    protected static function getACLColumns()
    {
        $cols = array('brandID', 'usergroupID', 'userID', 'controller', 'create', 'read', 'update', 'delete');
        return $cols;
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
        $ACL = new ACL();
        
        Errors::debugLogger(__METHOD__ . ': ' . $controller . '/' . $action, 90);
        // Allowed anonymous access:
        if (
                // Install Wizard
                ($controller == "install" && $action == "wizard") ||
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
        $test = $ACL->PermissionCheckUserLevel($userID, $controller, $crud);
        if ($test === TRUE) {
            return true;
        } elseif ($test === FALSE) {
            return self::ReturnFailedAuth($redirect);
        }

        // Group Defaults:
        $test = $ACL->PermissionCheckDefaultGroupLevel($usergroupID, $controller, $crud);
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
    public function PermissionCheckUserLevel($userID, $controller, $crud)
    {
        $results = self::getSingle(array('userID' => $userID,
            'controller' => $controller));

        if (!empty($results)) {

            Errors::debugLogger(__METHOD__ . ': Found user specific ACL (' . $results[$crud] . ')...', 90);
            if ($results[$crud] == 1) {
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
    public function PermissionCheckDefaultGroupLevel($usergroupID, $controller, $crud)
    {
        $results = self::getSingle(array('usergroupID' => $usergroupID,
            'controller' => $controller));

        if (!empty($results)) {
            Errors::debugLogger(__METHOD__ . ': Found group specific ACL (' . $results[$crud] . ')...', 90);
            if ($results[$crud] == 1) {
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
    
    public static function UpdateAccess($brandID, $usergroupID, $userID, $controller, $create, $read, $_update, $delete)
    {
        $ACL = new ACL();
        $ACL->update(array('brandID' => $brandID,
            'usergroupID' => $usergroupID,
            'userID' => $userID,
            'controller' => $controller,
            'create' => $create,
            'read' => $read,
            'update' => $_update,
            'delete' => $delete));
    }

}