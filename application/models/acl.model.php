<?php

class ACL extends Model
{

    protected static function getColumns()
    {
        $cols = array('brandID', 'usergroupID', 'userID', 'controller', 'create', 'read', 'update', 'delete');
        return $cols;
    }

    public function getWhere($where = NULL, $cols = NULL, $order = NULL, $aclWhere = NULL, $in = NULL, $loadChildren = FALSE)
    {
        if ($order == NULL)
        {
            $order = "brandID, usergroupID, userID, `controller`, `create`, `read`, `update`, `delete`";
        }
        return parent::getWhere($where, $cols, $order, $aclWhere, $in, $loadChildren);
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
    public static function IsUserAuthorized($_controller, $_action, $redirect = NULL)
    {
        $ACL = new ACL();

        Errors::debugLogger(__METHOD__ . ': ' . $_controller . '/' . $_action, 100);
        // Allowed anonymous access:
        if (
            $_controller == "help"
            || $_controller == "tests"
            || ($_controller == "install")
            || ($_controller == "home" && $_action == "home")
            || ($_controller == "users" && in_array($_action, array('login', 'logout', 'password')))
            || (empty($_SESSION['user'])
                && in_array($_controller, array('pages', 'articles', 'comments', 'stores', 'menus', 'menulinks'))
                && in_array($_action, array('read', 'readall')))
        )
        {
            return true;
        }

        #Errors::debugLogger("* Checking for Non-Anonymous User ACL *");
        if (empty($_SESSION['user']))
        {
            return self::ReturnFailedAuth($redirect);
        }

        // User is logged in, allow access to /admin/home (/owners) - template does rest
        // and Help
        if (($_controller == 'admin' && $_action == 'home' || $_controller == 'tools')
                || ($_controller == 'help' && $_action == 'home'))
        {
            return true;
        }

        /* User ACL check */
        $brandID     = BRAND_ID;
        $userID      = $_SESSION['user']['userID'];
        $usergroupID = $_SESSION['user']['usergroup']['usergroupID'];

        // CRUD taken from controller/action
        if ($_action == 'create')
        {
            $crud = "create";
        }
        elseif ($_action == 'readall' || $_action == 'read' || $_action == 'home')
        {
            $crud = "read";
        }
        elseif ($_action == 'update')
        {
            $crud = "update";
        }
        elseif ($_action == 'delete')
        {
            $crud = "delete";
        }

        // Search in order of user -> brand/group -> group defaults
        // Brand's User Defaults override?
        $test = $ACL->PermissionCheckUserLevel($userID, $_controller, $crud);
        if ($test === TRUE)
        {
            return true;
        }
        elseif ($test === FALSE)
        {
            return self::ReturnFailedAuth($redirect);
        }

        // Group Defaults:
        $test = $ACL->PermissionCheckDefaultGroupLevel($usergroupID, $_controller, $crud);
        if ($test === TRUE)
        {
            return true;
        }
        elseif ($test === FALSE)
        {
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
        $results = self::getSingle(array('userID'     => $userID,
                'controller' => $controller));

        if (!empty($results))
        {

            Errors::debugLogger(__METHOD__ . ': Found user specific ACL (' . $results[$crud] . ')...', 100);
            if ($results[$crud] == 1)
            {
                // Access explicitly ALLOWED:
                Errors::debugLogger(__METHOD__ . ': ACL APPROVED', 100);
                return true;
            }
            // Required access explicitly DENIED:
            Errors::debugLogger(__METHOD__ . ': ACL DENIED', 100);
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
                'controller'  => $controller));

        if (!empty($results))
        {
            Errors::debugLogger(__METHOD__ . ': Found group specific ACL (' . $results[$crud] . ')...', 100);
            if ($results[$crud] == 1)
            {
                // Access explicitly ALLOWED:
                Errors::debugLogger(__METHOD__ . ': ACL APPROVED', 100);
                return true;
            }
            // Required access explicitly DENIED:
            Errors::debugLogger(__METHOD__ . ': ACL DENIED', 100);
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
        Errors::debugLogger(__METHOD__);
        if ($redirect == "login" && empty($_SESSION['user']))
        {
            #$_SESSION['ErrorMessage'] = "Login Required";
            #$_SESSION['ErrorRedirect'] = NULL;
            #$_SESSION['ErrorRedirect'] = "/users/login?returnURL=/" . $_SESSION['controller'] . "/" . $_SESSION['action'];
            #Session::saveSessionToDB();
            Errors::debugLogger("* ACL Returning False -> Login *");
            if (!empty($_SESSION['controller'])
                    && !empty($_SESSION['action'])
                    && $_SESSION['controller'] == 'admin'
                    && $_SESSION['action'] == 'home')
            {
                $returnURL = "owners";
            }
            else
            {
                $q = "";
                if (!empty($_SESSION['query']))
                {
                    $q = "/" . implode('/', $_SESSION['query']);
                }
                if (!empty($_SESSION['controller'])
                    && !empty($_SESSION['action']))
                {
                    $returnURL = $_SESSION['controller'] . '/' . $_SESSION['action'] . $q;
                }
            }
            
            # Custom return URL from session (ie files)
            if (!empty($_SESSION['returnURL']))
            {
                $returnURL = $_SESSION['returnURL'];
            }
            
            Errors::debugLogger("Redirecting to login with returnURL: ".$returnURL);
            header('Location: /users/login?returnURL=/' . $returnURL);
            exit(0);
        }
        elseif ($redirect == "403" || ($redirect == "login" && !empty($_SESSION['user'])))
        {
            #$_SESSION['ErrorMessage'] = "Access Denied";
            #$_SESSION['ErrorRedirect'] = NULL;
            #$_SESSION['ErrorRedirect'] = "/users/login?access=1&returnURL=/" . $_SESSION['controller'] . "/" . $_SESSION['action'];
            #Session::saveSessionToDB();
            Errors::debugLogger("* ACL Returning False -> 403 *");
            header('Location: /users/login?access=1&returnURL=/' . $_SESSION['controller'] . '/' . $_SESSION['action']);
            exit(0);
        }
        else
        {
            Errors::debugLogger("* ACL Returning False -> False *");
            return false;
        }
    }

    public static function UpdateAccess($brandID, $usergroupID, $userID, $controller, $create, $read, $_update, $delete)
    {
        $ACL = new ACL();
        $ACL->update(array('brandID'     => $brandID,
            'usergroupID' => $usergroupID,
            'userID'      => $userID,
            'controller'  => $controller,
            'create'      => $create,
            'read'        => $read,
            'update'      => $_update,
            'delete'      => $delete));
    }

}