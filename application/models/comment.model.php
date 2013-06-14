<?php

class Comment extends Model
{
  protected static function getCommentColumns() {
    $cols = array('commentID', 'parentItemID', 'parentItemTypeID', 'commentActive', 'commentDatePublished', 'commentDateLastUpdated', 'userID');
    return $cols;
  }
  public function saveCommentInfo($data) {
    var_dump($data);
    return self::update($data);
  }

  public function getCommentInfo($commentID) {
    $cols = self::getCommentColumns();
    $where = 'commentID = '.$commentID;
    $res = self::select($cols, $where); // Dereferencing not available until php v5.4-5.5
    return $res[0];
  }
  
  public function getCommentIDs($where = NULL) {
    $cols = 'commentID';
    $order = 'commentID DESC';
    return self::select($cols, $where, $order);
  }
 
}