<?php

class Article extends Model
{
  public function getArticleTypeID() {
    $r = self::select("refParentItemTypeID", "parentTypeLabel = 'Article'", NULL, "refParentItemTypes");
    return $r[0]['refParentItemTypeID'];
  }  
  
  protected static function getArticleColumns() {
    $cols = array('articleID', 'articleActive', 'articleName', 'articleShortDescription', 'articleLongDescription',
        'articleDatePublished', 'articleDateLastReviewed', 'articleDateLastUpdated', 'articleDateExpires', 'userID', 'brandID');
    return $cols;
  }
  public function saveArticleInfo($data) {
    return self::update($data);
  }

  public function getArticleInfo($articleID, $LoadBrand = FALSE, $LoadUser = FALSE) {
    $cols = self::getArticleColumns();
    $where = 'articleID = '.$articleID;
    $res = self::select($cols, $where); // Dereferencing not available until php v5.4-5.5
    if ($LoadBrand)
    {
        $Brand = new Brand();
        $res['0']['brand'] = $Brand->getBrandInfo($res[0]['brandID']);
    }
    if ($LoadUser)
    {
        $User = new User();
        $res['0']['user'] = $User->getUserInfo($res[0]['userID']);
    }
    return $res[0];
  }
  
  public function getArticleIDs($where = NULL) {
    $cols = 'articleID';
    $order = 'articleID DESC';
    return self::select($cols, $where, $order);
  }
 
  public function getArticleComments($articleID, $commentID = NULL) {
    $cols = 'commentID, commentDatePublished';
    if (empty($commentID)) {
        $articleTypeID = $this->getArticleTypeID();
        $where      = 'parentItemID = ' . $articleID . ' AND parentItemTypeID = ' . $articleTypeID;
    } else {
        $Comment       = new Comment();
        $commentTypeID = $Comment->getCommentTypeID();
        $where         = 'parentItemID = ' . $commentID . ' AND parentItemTypeID = ' . $commentTypeID;
    }
    $order = 'commentDatePublished';
    $table = 'comments';
    return self::select($cols, $where, $order, $table);
  }
  
}