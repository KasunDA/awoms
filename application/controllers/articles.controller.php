<?php

class ArticlesController extends Controller
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
        // Article info and body are in separate tables
        if (in_array($k, array('inp_articleBody'))) {
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
        // Data array to be passed to sql
        $data                  = array();
        $data['articleActive'] = 1;
        $data['userID']        = $_SESSION['user']['userID'];

        // Post time
        $now                             = Utility::getDateTimeUTC();
        $data['articleDatePublished']    = $now;
        $data['articleDateLastReviewed'] = $now;
        $data['articleDateLastUpdated']  = $now;

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
        // Save article body
        $Article       = new Article();
        $bodyType      = $Article->getArticleTypeID();
        $bodyContentID = $Article->saveBodyContents($id, $bodyType, self::$staticData['inp_articleBody'], $_SESSION['user']['userID']);
        // Set previous bodies to inactive
        $Article->setBodyContentActive($id, $bodyType, $bodyContentID);
        return true;
    }

//    /**
//     * View
//     */
//    public function view()
//    {
//        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);
//        $getReq = func_get_args();
//        $this->set('title', 'Articles :: View');
//        if (empty($getReq[0])) {
//            $this->set('resultsMsg', 'Missing article ID...');
//            $this->set('article', NULL);
//            $this->set('articleBody', NULL);
//            return;
//        }
//        $articleID = $getReq[0];
//
//        // Get article info
//        $article = $this->Article->getArticleInfo($articleID);
//        if (empty($article) || $article['articleActive'] == 0) {
//            $this->set('resultsMsg', 'Article not found...');
//            $this->set('article', NULL);
//            $this->set('articleBody', NULL);
//            return;
//        }
//
//        // Get article body
//        $articleBody = $this->Article->getBodyContents($articleID, $this->Article->getArticleTypeID());
//
//        // Get article comments
//        $articleComments     = $this->Article->getArticleComments($articleID);
//        $articleCommentsList = array();
//
//        $comment = new Comment();
//        $commentTypeID = $comment->getCommentTypeID();
//
//        // Check each comment for children, construct ordered list with levels
//        for ($ac = 0; $ac < count($articleComments); $ac++) {
//            $tid         = $articleComments[$ac]['commentID'];
//            $thisComment = $comment->getBodyContents($tid, $commentTypeID, NULL, 1);
//            if (!empty($thisComment[0]['bodyContentText'])) {
//                $body = $thisComment[0]['bodyContentText'];
//            } else {
//                $body = "Empty......";
//            }
//            $commentDatePublished  = $articleComments[$ac]['commentDatePublished'];
//            $articleCommentsList[] = array('level'                => 1,
//                'commentID'            => $tid,
//                'commentDatePublished' => $commentDatePublished,
//                'commentBodyText'      => $body);
//
//            // Nested loop
//            $level = 1;
//            while (count($as    = $this->Article->getArticleComments($articleID, $tid)) > 0) {
//                $level++;
//                $tid                  = $as[0]['commentID'];
//                $commentDatePublished = $as[0]['commentDatePublished'];
//                $thisComment          = $comment->getBodyContents($tid, $commentTypeID, NULL, 1);
//                if (!empty($thisComment[0]['bodyContentText'])) {
//                    $body = $thisComment[0]['bodyContentText'];
//                } else {
//                    $body = "Empty......";
//                }
//                $articleCommentsList[] = array('level'                => $level,
//                    'commentID'            => $tid,
//                    'commentDatePublished' => $commentDatePublished,
//                    'commentBodyText'      => $body);
//            }
//        }
//
//        // Template data
//        $this->set('article', $article);
//        $this->set('articleBody', $articleBody);
//        $this->set('articleComments', $articleCommentsList);
//    }
}