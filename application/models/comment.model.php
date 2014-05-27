<?php

class Comment extends Model
{
  public function getCommentTypeID() {
    $r = self::select("refParentItemTypeID", "parentTypeLabel = 'Comment'", NULL, "refParentItemTypes");
    return $r[0]['refParentItemTypeID'];
  }  
    
  protected static function getCommentColumns() {
    $cols = array('commentID', 'parentItemID', 'parentItemTypeID', 'commentActive', 'commentDatePublished',
        'commentDateLastUpdated', 'userID');
    return $cols;
  }
  public function saveCommentInfo($data) {
    var_dump($data);
    return self::update($data);
  }

  public function getCommentInfo($commentID, $LoadUser = FALSE) {
    $cols = self::getCommentColumns();
    $where = 'commentID = '.$commentID;
    $res = self::select($cols, $where); // Dereferencing not available until php v5.4-5.5
    if ($LoadUser)
    {
        $User = new User();
        $res['0']['user'] = $User->getUserInfo($res[0]['userID']);
    }
    return $res[0];
  }
  
  public function getCommentIDs($where = NULL) {
    $cols = 'commentID';
    $order = 'commentID DESC';
    return self::select($cols, $where, $order);
  }
 
}