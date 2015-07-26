<?php

class MenuLink extends Model
{
    /**
     * Log Level
     *
     * @var int $logLevel Config log level used for this class
     */
    protected static $logLevel = 1000;

    protected static function getColumns()
    {
        $cols = array('linkID', 'menuID', 'sortOrder', 'parentLinkID', 'display', 'url', 'linkActive');
        return $cols;
    }

    /**
     * Returns all items of model (restricted by ACL)
     *
     * @param array|string $cols Columns to return if not *
     * @param string $order Order to return (col names)
     *
     * @return array
     */
    public function getWhere($where = NULL, $cols = NULL, $order = NULL, $aclWhere = NULL, $in = NULL, $loadChildren = FALSE)
    {
        if ($order == NULL)
        {
            $order = "menuID, parentLinkID, sortOrder, display, url";
        }

        // ACL: Ensure user only sees items they have permission to
        //$aclWhere          = array();
        //$aclWhere['where'] = NULL;
        //$aclWhere['in']    = NULL;

        // Admin: All
        // Store Owner:
        //
        // menulink.menuID
        //  => menus.menuID
        //      => menus.brandID
        //          => brands.brandID
        //              => usergroups.brandID
        //                  => user.usergroupID <-- SESSION['user']['usergroupID']
        //
        // ?       stores.ownerID = users.userID
        // Else: stores.brandID = users.brandID (done via model.class.aclWhere)

        if (!empty($_SESSION['user_logged_in'])
            && $_SESSION['user']['usergroup']['usergroupName'] == "Store Owners")
        {
            //Errors::debugLogger("Store Owner is logged on... appending AclWhere...", MenuLink::$logLevel);
            //$aclWhere['where'] = array('ownerID' => $_SESSION['user']['userID']);
            //$aclWhere['in']    = NULL;
        }

        return parent::getWhere($where, $cols, $order, $aclWhere, $in, $loadChildren);
    }

}