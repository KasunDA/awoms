<?php

class PagesController extends Controller
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
        // Page info and body are in separate tables
        if (in_array($k, array('inp_pageBody'))) {
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
        $data               = array();
        $data['pageActive'] = 1;
        $data['userID']     = $_SESSION['user']['userID'];

        // Post time
        $now                          = Utility::getDateTimeUTC();
        $data['pageDatePublished']    = $now;
        $data['pageDateLastReviewed'] = $now;
        $data['pageDateLastUpdated']  = $now;

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
        // Save page body
        $Page          = new Page();
        $bodyType      = $Page->getPageTypeID();
        $bodyContentID = $Page->saveBodyContents($id, $bodyType, self::$staticData['inp_pageBody'], $_SESSION['user']['userID']);
        // Set previous bodies to inactive
        $Page->setBodyContentActive($id, $bodyType, $bodyContentID);
        return true;
    }

//    /**
//     * View
//     */
//    public function view()
//    {
//        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);
//        $getReq = func_get_args();
//        $this->set('title', 'Pages :: View');
//        if (empty($getReq[0])) {
//            $this->set('resultsMsg', 'Missing page ID...');
//            $this->set('page', NULL);
//            $this->set('pageBody', NULL);
//            return;
//        }
//        $pageID = $getReq[0];
//
//        // Get page info
//        $page = $this->Page->getPageInfo($pageID);
//        if (empty($page)
//                || $page['pageActive'] == 0)
//                // ACL @TODO
//                // || $page['brandID'] != BRAND_ID)
//        {
//            $this->set('resultsMsg', 'Page not found...');
//            $this->set('ACLAllowed', FALSE);            
//            $this->set('page', NULL);
//            $this->set('pageBody', NULL);
//            return;
//        }
//
//        // Get page body
//        $pageBody = $this->Page->getBodyContents($pageID, $this->Page->getPageTypeID());
//
//        // Get page comments
//        $pageComments     = $this->Page->getPageComments($pageID);
//        $pageCommentsList = array();
//
//        $comment = new Comment();
//        $commentTypeID = $comment->getCommentTypeID();
//
//        // Check each comment for children, construct ordered list with levels
//        for ($ac = 0; $ac < count($pageComments); $ac++) {
//            $tid         = $pageComments[$ac]['commentID'];
//            $thisComment = $comment->getBodyContents($tid, $commentTypeID, NULL, 1);
//            if (!empty($thisComment[0]['bodyContentText'])) {
//                $body = $thisComment[0]['bodyContentText'];
//            } else {
//                $body = "Empty......";
//            }
//            $commentDatePublished = $pageComments[$ac]['commentDatePublished'];
//            $pageCommentsList[]   = array('level'                => 1,
//                'commentID'            => $tid,
//                'commentDatePublished' => $commentDatePublished,
//                'commentBodyText'      => $body);
//
//            // Nested loop
//            $level = 1;
//            while (count($as    = $this->Page->getPageComments($pageID, $tid)) > 0) {
//                $level++;
//                $tid                  = $as[0]['commentID'];
//                $commentDatePublished = $as[0]['commentDatePublished'];
//                $thisComment          = $comment->getBodyContents($tid, $commentTypeID, NULL, 1);
//                if (!empty($thisComment[0]['bodyContentText'])) {
//                    $body = $thisComment[0]['bodyContentText'];
//                } else {
//                    $body = "Empty......";
//                }
//                $pageCommentsList[] = array('level'                => $level,
//                    'commentID'            => $tid,
//                    'commentDatePublished' => $commentDatePublished,
//                    'commentBodyText'      => $body);
//            }
//        }
//
//        // Template data
//        $this->set('page', $page);
//        $this->set('pageBody', $pageBody);
//        $this->set('pageComments', $pageCommentsList);
//    }
}