<?php

class Article extends Model
{
  protected static function getArticleColumns() {
    $cols = array('articleID', 'articleActive', 'articleName', 'articleShortDescription', 'articleLongDescription', 'articleDatePublished', 'articleDateLastReviewed', 'articleDateLastUpdated', 'articleDateExpires', 'userID');
    return $cols;
  }
  public function saveArticleInfo($data) {
    return self::update($data);
  }

  public function getArticleInfo($articleID) {
    $cols = self::getArticleColumns();
    $where = 'articleID = '.$articleID;
    return self::select($cols, $where)[0];
  }
  
  public function getArticleIDs($where = NULL) {
    $cols = 'articleID';
    $order = 'articleID DESC';
    return self::select($cols, $where, $order);
  }

  
}