<?php

class Page extends Model
{
    protected static function getPageColumns()
    {
        $cols = array('pageID', 'pageActive', 'pageName', 'pageShortDescription', 'pageLongDescription', 'pageDatePublished', 'pageDateLastReviewed',
            'pageDateLastUpdated', 'pageDateExpires', 'userID', 'brandID');
        return $cols;
    }

    public function getPageTypeID()
    {
        $r = self::select("refParentItemTypeID", "parentTypeLabel = 'Page'", NULL, "refParentItemTypes");
        return $r[0]['refParentItemTypeID'];
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
        return $item;
    }

    private static function getPageBody($pageID)
    {
        // Page Type ID
        $Page       = new Page();
        $pageTypeID = $Page->getPageTypeID();

        // BodyContent (Page)
        $BodyContent = new BodyContent();
        return $BodyContent->getWhere(
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
        $res         = $BodyContent->getWhere(
                array(
                    'parentItemTypeID'  => $commentTypeID,
                    'parentItemID'      => $pageID,
                    'bodyContentActive' => 1));
        return $res;

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