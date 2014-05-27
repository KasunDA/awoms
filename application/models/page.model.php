<?php

class Page extends Model
{
    protected static function getPageColumns()
    {
        $cols = array('pageID', 'pageActive', 'pageName', 'pageShortDescription', 'pageLongDescription', 'pageDatePublished', 'pageDateLastReviewed',
            'pageDateLastUpdated', 'pageDateExpires', 'userID', 'brandID');
        return $cols;
    }
    
  public function getPageTypeID() {
    $r = self::select("refParentItemTypeID", "parentTypeLabel = 'Page'", NULL, "refParentItemTypes");
    return $r[0]['refParentItemTypeID'];
  }  

    public function savePageInfo($data)
    {
        return self::update($data);
    }

    public function getPageInfo($pageID, $LoadBrand = FALSE, $LoadUser = FALSE)
    {
        $cols  = self::getPageColumns();
        $where = 'pageID = ' . $pageID;
        $res   = self::select($cols, $where); // Dereferencing not available until php v5.4-5.5
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

    public function getPageIDs($where = NULL)
    {
        $cols  = 'pageID';
        $order = 'pageID DESC';
        return self::select($cols, $where, $order);
    }

    public function getPageComments($pageID, $commentID = NULL)
    {
        $cols = 'commentID, commentDatePublished';
        if (empty($commentID)) {
            $Page       = new Page();
            $pageTypeID = $Page->getPageTypeID();
            $where      = 'parentItemID = ' . $pageID . ' AND parentItemTypeID = ' . $pageTypeID;
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