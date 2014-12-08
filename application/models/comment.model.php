<?php

class Comment extends Model
{
    protected static function getColumns()
    {
        $cols = array('commentID', 'parentItemID', 'parentItemTypeID', 'commentActive', 'commentDatePublished',
            'commentDateLastUpdated', 'userID');
        return $cols;
    }

    public function getCommentTypeID()
    {
        $r = self::select("refParentItemTypeID", "parentTypeLabel = 'Comment'", NULL, "refParentItemTypes");
        return $r[0]['refParentItemTypeID'];
    }

    public function getWhere($where = NULL, $cols = NULL, $order = NULL, $aclWhere = NULL, $in = NULL, $loadChildren = FALSE)
    {
        if ($order == NULL) {
            $order = "parentItemID, commentDatePublished";
        }
        return parent::getWhere($where, $cols, $order, $aclWhere, $in, $loadChildren);
    }

}