<?php

class CommentsController extends Controller
{
    public function __construct($controller, $model, $action, $template = NULL)
    {
        parent::__construct($controller, $model, $action, $template);
    }
    
    public static $staticData = array();

    /**
     * Controller specific input filtering on save, for use with StepFinish method
     * 
     * e.g. pulls out inp_pageBody and saves that after page has been saved via StepFinish method
     * 
     * @param string $k
     * @param array|string $v
     * 
     * @return boolean True confirms match | False nothing was done
     */
    public static function createStepInput($k, $v)
    {
        // Spam protection
        if ($k == 'botrequired') {
            if ($v != '') {
                Errors::debugLogger(1, $_POST);
                trigger_error('Big success! ;)', E_USER_ERROR);
                exit;
            }
        }
        
        // Comment info and body are in separate tables
        if (in_array($k, array('inp_commentBody', 'parentItemID', 'parentItemTypeID'))) {
            self::$staticData[$k] = $v;
            return true;
        }
        return false;
    }
    
    /**
     * Controller specific pre-save method
     * 
     * e.g. inserts controller specific data such as page datetime, isActive
     * 
     * @return array data to be merged into model->save($data)
     */
    public static function createStepPreSave()
    {
        $data = array();
        $data['userID'] = $_SESSION['user']['userID'];

        // @TODO
        // Parent Item Type ID
        //$data['parentItemTypeID'] = "?";
        
//        $getReq = func_get_args();
//        // Selected Article
//        if (!empty($getReq[0])) {
//            $articleID        = $getReq[0];
//            $this->set('articleID', $articleID);
//            $parentItemID     = $articleID;
//            $parentItemTypeID = 1; // Article=1, Comment=2
//            // Selected Comment
//            if (!empty($getReq[1])) {
//                $parentCommentID  = $getReq[1];
//                $this->set('parentCommentID', $parentCommentID);
//                $parentItemID     = $parentCommentID;
//                $parentItemTypeID = 2; // Article=1, Comment=2
//            }
//
//            $this->set('parentItemID', $parentItemID);
//        } else {
//            $this->set('parentItemID', NULL);
//        }
        
        // Post time
        $now = Utility::getDateTimeUTC();
        $data['commentDatePublished']   = $now;
        $data['commentDateLastUpdated'] = $now;
        
        return $data;
    }

    /**
     * Controller specific finish Create step after first input save
     * 
     * e.g. use self::$staticData in other model save methods (page->body)
     * 
     * @param string $id ID of parent item
     * 
     * @return boolean
     */
    public static function createStepFinish($id)
    {
        // Save comment body
        $Comment = new Comment();
        $bodyType      = $Comment->getCommentTypeID();
        $bodyContentID = $Comment->saveBodyContents($id, $bodyType, self::$staticData['inp_commentBody'], $_SESSION['user']['userID']);
        
        // Set previous bodies to inactive
        $Comment->setBodyContentActive($id, $bodyType, $bodyContentID);
        return true;
    }
}