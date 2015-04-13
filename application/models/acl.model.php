<?php

class ACL extends Model
{

    /**
     * Log Level
     *
     * @var int $logLevel Config log level; 0 would always be logged, 9999 would only be logged in Dev
     */
    protected static $logLevel = 8000;

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
        Errors::debugLogger(__METHOD__.'@'.__LINE__.': ' . $_controller . '/' . $_action . ' (redirect: ' . $redirect . ')', ACL::$logLevel);
        $ACL = new ACL();

        // Allowed anonymous access:
        if (
            $_controller == "help"
            || $_controller == "tests"
            || ($_controller == "install")
            || ($_controller == "home" && $_action == "home")
            || ($_controller == "users" && in_array($_action, array('login', 'logout', 'password')))
            || ($_controller == "stores" && !in_array($_action, array('create', 'update', 'delete'))) // for /stores/locations/x rewrite alias
            || (empty($_SESSION['user'])
                && in_array($_controller, array(
                    'pages',
                    'articles',
                    'comments',
                    'menus',
                    'menulinks'))
                    && in_array($_action, array('read', 'readall')))
        )
        {
            Errors::debugLogger(__METHOD__.'@'.__LINE__.': Allowed Anonymous access', ACL::$logLevel);
            return true;
        }

        if (empty($_SESSION['user']))
        {
            Errors::debugLogger(__METHOD__.'@'.__LINE__.': Denied Anonymous access', ACL::$logLevel);
            return self::ReturnFailedAuth($redirect);
        }

        // User is logged in, allow access to /admin/home (/owners) - template does rest
        // and Help
        if (($_controller == 'admin' && $_action == 'home')
                || $_controller == 'tools'
                || ($_controller == 'help' && $_action == 'home'))
        {
            Errors::debugLogger(__METHOD__.'@'.__LINE__.": User is logged in, allowed basic pages #001", ACL::$logLevel);
            return true;
        }

        /* User ACL check */
        $brandID     = BRAND_ID;
        $userID      = $_SESSION['user']['userID'];
        $usergroupID = $_SESSION['user']['usergroup']['usergroupID'];

        // CRUD taken from controller/action
        $crud = "read"; // for aliases default to read
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

        // ACL Deny Search in order of User -> Brand/Group -> Group defaults
        // Brand's User Defaults override?
        $test = $ACL->PermissionCheckUserLevel($userID, $_controller, $crud);
        if ($test === TRUE)
        {
            Errors::debugLogger(__METHOD__.'@'.__LINE__.": * Passed User Level Check *", ACL::$logLevel);
            return true;
        }
        elseif ($test === FALSE)
        {
            Errors::debugLogger(__METHOD__.'@'.__LINE__.": * Failed User Level Check *", ACL::$logLevel);
            return self::ReturnFailedAuth($redirect);
        }

        // Group Defaults:
        $test = $ACL->PermissionCheckDefaultGroupLevel($usergroupID, $_controller, $crud);
        if ($test === TRUE)
        {
            Errors::debugLogger(__METHOD__.'@'.__LINE__.": * Passed Group Level Check *", ACL::$logLevel);
            return true;
        }
        elseif ($test === FALSE)
        {
            Errors::debugLogger(__METHOD__.'@'.__LINE__.": * Failed Group Level Check *", ACL::$logLevel);
            return self::ReturnFailedAuth($redirect);
        }

        // No entry found for allow or deny so returning false (deny)
        Errors::debugLogger(__METHOD__.'@'.__LINE__.": * Failed *", ACL::$logLevel);
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

            Errors::debugLogger(__METHOD__ . ': Found user specific ACL (' . $results[$crud] . ')...', ACL::$logLevel);
            if ($results[$crud] == 1)
            {
                // Access explicitly ALLOWED:
                Errors::debugLogger(__METHOD__ . ': ACL APPROVED', ACL::$logLevel);
                return true;
            }
            // Required access explicitly DENIED:
            Errors::debugLogger(__METHOD__ . ': ACL DENIED', ACL::$logLevel);
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
            Errors::debugLogger(__METHOD__ . ': Found group specific ACL (' . $results[$crud] . ')...', ACL::$logLevel);
            if ($results[$crud] == 1)
            {
                // Access explicitly ALLOWED:
                Errors::debugLogger(__METHOD__ . ': ACL APPROVED', ACL::$logLevel);
                return true;
            }
            // Required access explicitly DENIED:
            Errors::debugLogger(__METHOD__ . ': ACL DENIED', ACL::$logLevel);
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
        Errors::debugLogger(__METHOD__.'@'.__LINE__, ACL::$logLevel);
        if ($redirect == "login" && empty($_SESSION['user']))
        {
            #$_SESSION['ErrorMessage'] = "Login Required";
            #$_SESSION['ErrorRedirect'] = NULL;
            #$_SESSION['ErrorRedirect'] = "/users/login?returnURL=/" . $_SESSION['controller'] . "/" . $_SESSION['action'];
            #Session::saveSessionToDB();
            Errors::debugLogger(__METHOD__.'@'.__LINE__.": * Login Required, User Not Logged In = ACL Returning False -> Login *", ACL::$logLevel, TRUE);
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
            
            Errors::debugLogger(__METHOD__.'@'.__LINE__.": ReturnURL: ".$returnURL, ACL::$logLevel);
            header('Location: /users/login?returnURL=/' . $returnURL);
            exit(0);
        }
        elseif ($redirect == "403" || ($redirect == "login" && !empty($_SESSION['user'])))
        {
            #$_SESSION['ErrorMessage'] = "Access Denied";
            #$_SESSION['ErrorRedirect'] = NULL;
            #$_SESSION['ErrorRedirect'] = "/users/login?access=1&returnURL=/" . $_SESSION['controller'] . "/" . $_SESSION['action'];
            #Session::saveSessionToDB();
            Errors::debugLogger(__METHOD__.'@'.__LINE__.": * ACL Returning False -> 403 *", ACL::$logLevel, TRUE);
            header('Location: /users/login?access=1&returnURL=/' . $_SESSION['controller'] . '/' . $_SESSION['action']);
            exit(0);
        }
        else
        {
            Errors::debugLogger(__METHOD__.'@'.__LINE__.": * ACL Returning False -> False *", ACL::$logLevel, TRUE);
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