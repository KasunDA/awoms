<?php

class ArticlesController extends Controller
{
    public function __construct($controller, $model, $action, $template = NULL)
    {
        parent::__construct($controller, $model, $action, $template);
    }

    /**
     * View All
     */
    public function viewall()
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);

        // Template data
        $this->set('title', 'Articles :: View All');

        // Get articles list
        $this->getArticles();

        // Prepare Create Form
        parent::prepareForm(NULL, "ALL");
    }

    /**
     * Create
     */
    public function create()
    {
        $this->set('title', 'Articles :: Create');

        // Step 1: Create/Edit form
        if ($this->step == 1) {

            // Prepare Create Form
            parent::prepareForm(NULL, "ALL");
        }

        // Step 2: Save article
        elseif ($this->step == 2) {

            // Data array to be passed to sql
            $data                  = array();
            $data['articleActive'] = 1;

            // @todo Not sure where to put user ID.. 1=anon
            $data['userID'] = 1;

            // Post time
            $now                             = Utility::getDateTimeUTC();
            $data['articleDatePublished']    = $now;
            $data['articleDateLastReviewed'] = $now;
            $data['articleDateLastUpdated']  = $now;

            // Gets input data from post, must begin with "inp_"
            foreach ($_POST as $k => $v) {
                if (!preg_match('/^inp_(.*)/', $k, $m)) {
                    continue;
                }
                $this->set($k, $v);

                // Article id (new or existing)
                if ($k == 'inp_articleID') {
                    $inp_articleID = $v;
                }

                // Article info and body are in separate tables
                if ($k == 'inp_articleBody') {
                    $inp_articleBody = $v;
                    continue;
                }

                // Article info col/data
                $data[$m[1]] = $v;
            }

            // Save article info, getting ID
            $articleID = $this->Article->saveArticleInfo($data);
            if ($inp_articleID != 'DEFAULT') {
                // Updated (existing ID)
                $articleID = $inp_articleID;
            }

            $this->set('articleID', $articleID);

            // Save article body
            $bodyType = $this->Article->getArticleTypeID();
            $bodyContentID = $this->Article->saveBodyContents($articleID, $bodyType, $inp_articleBody, $data['userID']);
            $this->set('bodyContentID', $bodyContentID);

            // Set previous bodies to inactive
            $this->Article->setBodyContentActive($articleID, $bodyType, $bodyContentID);
            
            $this->set('success', TRUE);
        }

        // Get updated list
        $this->getArticles();
    }

    /**
     * Edit
     */
    public function edit()
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);

        $this->set('title', 'Articles :: Edit');

        $args = func_get_args();
        if (!empty($args)) {
            $ID = $args[0];
            $this->set('articleID', $ID);
        }

        $res = TRUE;
        if ($this->step == 1) {
            // Get article info
            $article = $this->Article->getArticleInfo($ID);
            $this->set('article', $article);

            // Get article body
            $articleBody = $this->Article->getBodyContents($ID, $this->Article->getArticleTypeID());
            $this->set('articleBody', $articleBody);

            // Get article list
            $this->getArticles();

            // Template variables
            foreach ($article as $k => $v) {
                if ($k == "brandID") {
                    $selectedBrandID = $v;
                }
                $this->set("inp_" . $k, $v);
            }

            foreach ($articleBody as $body) {
                if ($body['bodyContentActive'] == 1) {
                    $this->set('inp_articleBody', $body);
                    break;
                }
            }

            // Prepare Create Form
            parent::prepareForm($ID, $selectedBrandID);
        } elseif ($this->step == 2) {
            // Use create method to edit existing
            $res = $this->create();
        }

        return $res;
    }

    /**
     * Get Articles
     */
    public function getArticles()
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);

        // "Top 10" or similar latest LIMIT <--- @todo
        // @todo arguments
        // $getReq = func_get_args();  
        // Get all article IDs (active only)
        $articleIDs = $this->Article->getArticleIDs('articleActive=1');
        $articles   = array();

        // Get all article info
        foreach ($articleIDs as $a) {
            $article    = $this->Article->getArticleInfo($a['articleID']);
            $articles[] = $article;
        }

        // Return false if no articles
        if (empty($articles)) {
            $this->set('articles', NULL);
            $this->set('articleBodies', NULL);
            return false;
        }

        $articleTypeID = $this->Article->getArticleTypeID();
                
        // Get all article bodies (active only)
        foreach ($articleIDs as $a) {
            $articleBody     = $this->Article->getBodyContents($a['articleID'], $articleTypeID, NULL, 1);
            $articleBodies[] = $articleBody;
        }

        // Return data
        $this->set('articles', $articles);
        $this->set('articleBodies', $articleBodies);
        $res['articles']      = $articles;
        $res['articleBodies'] = $articleBodies;
        return $res;
    }

    /**
     * View
     */
    public function view()
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);
        $getReq = func_get_args();
        $this->set('title', 'Articles :: View');
        if (empty($getReq[0])) {
            $this->set('resultsMsg', 'Missing article ID...');
            $this->set('article', NULL);
            $this->set('articleBody', NULL);
            return;
        }
        $articleID = $getReq[0];

        // Get article info
        $article = $this->Article->getArticleInfo($articleID);
        if (empty($article) || $article['articleActive'] == 0) {
            $this->set('resultsMsg', 'Article not found...');
            $this->set('article', NULL);
            $this->set('articleBody', NULL);
            return;
        }

        // Get article body
        $articleBody = $this->Article->getBodyContents($articleID, $this->Article->getArticleTypeID());

        // Get article comments
        $articleComments     = $this->Article->getArticleComments($articleID);
        $articleCommentsList = array();

        $comment = new Comment();
        $commentTypeID = $comment->getCommentTypeID();

        // Check each comment for children, construct ordered list with levels
        for ($ac = 0; $ac < count($articleComments); $ac++) {
            $tid         = $articleComments[$ac]['commentID'];
            $thisComment = $comment->getBodyContents($tid, $commentTypeID, NULL, 1);
            if (!empty($thisComment[0]['bodyContentText'])) {
                $body = $thisComment[0]['bodyContentText'];
            } else {
                $body = "Empty......";
            }
            $commentDatePublished  = $articleComments[$ac]['commentDatePublished'];
            $articleCommentsList[] = array('level'                => 1,
                'commentID'            => $tid,
                'commentDatePublished' => $commentDatePublished,
                'commentBodyText'      => $body);

            // Nested loop
            $level = 1;
            while (count($as    = $this->Article->getArticleComments($articleID, $tid)) > 0) {
                $level++;
                $tid                  = $as[0]['commentID'];
                $commentDatePublished = $as[0]['commentDatePublished'];
                $thisComment          = $comment->getBodyContents($tid, $commentTypeID, NULL, 1);
                if (!empty($thisComment[0]['bodyContentText'])) {
                    $body = $thisComment[0]['bodyContentText'];
                } else {
                    $body = "Empty......";
                }
                $articleCommentsList[] = array('level'                => $level,
                    'commentID'            => $tid,
                    'commentDatePublished' => $commentDatePublished,
                    'commentBodyText'      => $body);
            }
        }

        // Template data
        $this->set('article', $article);
        $this->set('articleBody', $articleBody);
        $this->set('articleComments', $articleCommentsList);
    }

}