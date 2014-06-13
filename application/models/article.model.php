<?php

class Article extends Model
{
    protected static function getArticleColumns()
    {
        $cols = array('articleID', 'articleActive', 'articleName', 'articleShortDescription', 'articleLongDescription',
            'articleDatePublished', 'articleDateLastReviewed', 'articleDateLastUpdated', 'articleDateExpires', 'userID', 'brandID');
        return $cols;
    }

    public function getArticleTypeID()
    {
        $r = self::select("refParentItemTypeID", "parentTypeLabel = 'Article'", NULL, "refParentItemTypes");
        return $r[0]['refParentItemTypeID'];
    }

    /**
     * Load additional model specific info when getWhere is called
     * 
     * @param type $item
     */
    public static function LoadExtendedItem($item)
    {
        $item['articleBody'] = self::getArticleBody($item['articleID']);
        $item['comments']    = self::getArticleComments($item['articleID']);
        return $item;
    }

    private static function getArticleBody($articleID)
    {
        // Article Type ID
        $Article       = new Article();
        $articleTypeID = $Article->getArticleTypeID();

        // BodyContent (Article)
        $BodyContent = new BodyContent();
        return $BodyContent->getWhere(
                        array(
                            'parentItemTypeID'  => $articleTypeID,
                            'parentItemID'      => $articleID,
                            'bodyContentActive' => 1));
    }

    private static function getArticleComments($articleID, $commentID = NULL)
    {
        // Comment Type ID
        $Comment       = new Comment();
        $commentTypeID = $Comment->getCommentTypeID();

        // BodyContent (Comment)
        $BodyContent = new BodyContent();
        $res         = $BodyContent->getWhere(
                array(
                    'parentItemTypeID'  => $commentTypeID,
                    'parentItemID'      => $articleID,
                    'bodyContentActive' => 1));
        return $res;
    }

}