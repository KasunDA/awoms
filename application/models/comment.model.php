<?php

class Comment extends Model
{
    protected static function getCommentColumns()
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

}