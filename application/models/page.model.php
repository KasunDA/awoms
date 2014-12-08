<?php

class Page extends Model
{
    protected static function getColumns()
    {
        $cols = array('pageID', 'pageActive', 'pageName', 'pageShortDescription', 'pageLongDescription', 'pageDatePublished', 'pageDateLastReviewed',
            'pageDateLastUpdated', 'pageDateExpires', 'userID', 'brandID', 'pageJavaScript', 'pageJavaScript', 'pageShowTitle', 'pageRestricted');
        return $cols;
    }

    public function getPageTypeID()
    {
        $r = self::select("refParentItemTypeID", "parentTypeLabel = 'Page'", NULL, "refParentItemTypes");
        return $r[0]['refParentItemTypeID'];
    }

    public function getWhere($where = NULL, $cols = NULL, $order = NULL, $aclWhere = NULL, $in = NULL, $loadChildren = FALSE)
    {
        if ($order == NULL)
        {
            $order = "brandID, pageName, pageDatePublished";
        }
        
        $all = parent::getWhere(NULL, $cols, $order, $aclWhere);
        
        $i = 0;
        foreach ($all as $_page)
        {
            // ACL: Ensures user has permission to view requested page
            if (!empty($_page['pageRestricted']))
            {
                // Page is restricted
                if (empty($_SESSION['user_logged_in'])
                        || !in_array($_SESSION['user']['usergroup']['usergroupName'], array('Administrators', 'Store Owners')))
                {
                    // Access Denied
                    unset($all[$i]);
                }
            }
            $i++;
        }

        return $all;
    }
    
    /**
     * Load additional model specific info when getWhere is called
     * 
     * @param type $item
     */
    public static function LoadExtendedItem($item)
    {
        $item['pageBody'] = self::getPageBody($item['pageID']);
        $item['comments'] = self::getPageComments($item['pageID']);
        
        // Load the item's brand
        //$Brand         = new Brand();
        //$item['brand'] = $Brand->getSingle(array('brandID'     => $item['brandID'], 'brandActive' => 1));

        return $item;
    }

    private static function getPageBody($pageID)
    {
        // Page Type ID
        $Page       = new Page();
        $pageTypeID = $Page->getPageTypeID();

        // BodyContent (Page)
        $BodyContent = new BodyContent();
        return $BodyContent->getSingle(
                        array(
                            'parentItemTypeID'  => $pageTypeID,
                            'parentItemID'      => $pageID,
                            'bodyContentActive' => 1));
    }

    private static function getPageComments($pageID, $commentID = NULL)
    {
        // Comment Type ID
        $Comment       = new Comment();
        $commentTypeID = $Comment->getCommentTypeID();

        // BodyContent (Comment)
        $BodyContent = new BodyContent();
        return $BodyContent->getWhere(
                array(
                    'parentItemTypeID'  => $commentTypeID,
                    'parentItemID'      => $pageID,
                    'bodyContentActive' => 1));

//        $cols = 'commentID, commentDatePublished';
//        if (empty($commentID)) {
//            $Page       = new Page();
//            $pageTypeID = $Page->getPageTypeID();
//            $where      = 'parentItemID = ' . $pageID . ' AND parentItemTypeID = ' . $pageTypeID;
//        } else {
//            $Comment       = new Comment();
//            $commentTypeID = $Comment->getCommentTypeID();
//            $where         = 'parentItemID = ' . $commentID . ' AND parentItemTypeID = ' . $commentTypeID;
//        }
//        $order = 'commentDatePublished';
//        $table = 'comments';
//        return self::select($cols, $where, $order, $table);
    }

}