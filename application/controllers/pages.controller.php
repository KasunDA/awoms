<?php

class PagesController extends Controller
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
        $this->set('title', 'Pages :: View All');

        // Get pages list
        $this->getPages();
        
        // ACL: Allowed to view list by default
        //$this->set('ACLAllowed', TRUE);

        // Prepare Create Form
        parent::prepareForm(NULL, "ALL");
    }

    /**
     * Create
     */
    public function create()
    {

        $this->set('title', 'Pages :: Create');

        // Step 1: Create/Edit form
        if ($this->step == 1) {
            
            // Prepare Create Form
            parent::prepareForm(NULL, "ALL");

        }

        // Step 2: Save page
        elseif ($this->step == 2) {

            // Data array to be passed to sql
            $data               = array();
            $data['pageActive'] = 1;

            // @TODO Not sure where to put user ID.. 1=anon
            $data['userID'] = 1;
            
            // @TODO: Ensure brandID hasn't been changed (post.inp_brandID should == BRAND_ID)
            
            
            // Post time
            $now                          = Utility::getDateTimeUTC();
            $data['pageDatePublished']    = $now;
            $data['pageDateLastReviewed'] = $now;
            $data['pageDateLastUpdated']  = $now;

            // Gets input data from post, must begin with "inp_"
            foreach ($_POST as $k => $v) {
                if (!preg_match('/^inp_(.*)/', $k, $m)) {
                    continue;
                }

                // ACL
//                if ($k == "inp_brandID")
//                {
//                    if ($v != BRAND_ID)
//                    {
//                        // Final success status (failure)
//                        $this->set('success', "Not allowed to post to this brand...");
//                        $this->set('ACLAllowed', FALSE);
//                        return false;
//                    }
//                    else
//                    {
//                        $this->set('ACLAllowed', TRUE);
//                    }
//                }
                
                $this->set($k, $v);

                // Page id (new or existing)
                if ($k == 'inp_pageID') {
                    $inp_pageID = $v;
                }

                // Page info and body are in separate tables
                if ($k == 'inp_pageBody') {
                    $inp_pageBody = $v;
                    continue;
                }

                // Page info col/data
                $data[$m[1]] = $v;
            }

            // Save page info, getting ID
            $pageID = $this->Page->savePageInfo($data);
            if ($inp_pageID != 'DEFAULT') {
                // Updated (existing ID)
                $pageID = $inp_pageID;
            }

            $this->set('pageID', $pageID);

            // Save page body
            $bodyType = $this->Page->getPageTypeID();
            $bodyContentID = $this->Page->saveBodyContents($pageID, $bodyType, $inp_pageBody, $data['userID']);
            $this->set('bodyContentID', $bodyContentID);

            // Set previous bodies to inactive
            $this->Page->setBodyContentActive($pageID, $bodyType, $bodyContentID);
            
            // Final success status
            $this->set('success', TRUE);
        }

        // Get updated list
        $this->getPages();
    }

    /**
     * Edit
     */
    public function edit()
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);

        $this->set('title', 'Pages :: Edit');

        $args = func_get_args();
        if (!empty($args)) {
            $ID = $args[0];
            $this->set('pageID', $ID);
        }
        
        $res = TRUE;
        if ($this->step == 1) {
            // Get page info
            $page = $this->Page->getPageInfo($ID);
            
            // ACL
//            if ($page['brandID'] != BRAND_ID) {
//                $this->set('resultsMsg', "Page not found...");
//                $this->set('ACLAllowed', FALSE);
//                return;
//            }
            
            $this->set('page', $page);

            // Get page body
            $pageBody = $this->Page->getBodyContents($ID, $this->Page->getPageTypeID());
            $this->set('pageBody', $pageBody);

            // Get page list
            $this->getPages();

            // Template variables
            foreach ($page as $k => $v) {
                if ($k == "brandID") {
                    $selectedBrandID = $v;
                }
                $this->set("inp_" . $k, $v);
            }
            foreach ($pageBody as $body) {
                if ($body['bodyContentActive'] == 1) {
                    $this->set('inp_pageBody', $body);
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
     * Get Pages
     */
    public function getPages()
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);

        // Get all article IDs (active only, this brand only)
        // ACL @TODO
        $pageIDs = $this->Page->getPageIDs('pageActive=1'); // AND brandID='.BRAND_ID);
        $pages   = array();

        // Get all page info
        foreach ($pageIDs as $a) {
            $page    = $this->Page->getPageInfo($a['pageID']);
            $pages[] = $page;
        }

        // Return false if no pages
        if (empty($pages)) {
            $this->set('pages', NULL);
            $this->set('pageBodies', NULL);
            return false;
        }

        // Get all page bodies (active only)
        foreach ($pageIDs as $a) {
            $pageBody     = $this->Page->getBodyContents($a['pageID'], $this->Page->getPageTypeID(), NULL, 1);
            $pageBodies[] = $pageBody;
        }

        // Return data
        $this->set('pages', $pages);
        $this->set('pageBodies', $pageBodies);
        $res['pages']      = $pages;
        $res['pageBodies'] = $pageBodies;
        return $res;
    }

    /**
     * View
     */
    public function view()
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);
        $getReq = func_get_args();
        $this->set('title', 'Pages :: View');
        if (empty($getReq[0])) {
            $this->set('resultsMsg', 'Missing page ID...');
            $this->set('page', NULL);
            $this->set('pageBody', NULL);
            return;
        }
        $pageID = $getReq[0];

        // Get page info
        $page = $this->Page->getPageInfo($pageID);
        if (empty($page)
                || $page['pageActive'] == 0)
                // ACL @TODO
                // || $page['brandID'] != BRAND_ID)
        {
            $this->set('resultsMsg', 'Page not found...');
            $this->set('ACLAllowed', FALSE);            
            $this->set('page', NULL);
            $this->set('pageBody', NULL);
            return;
        }

        // Get page body
        $pageBody = $this->Page->getBodyContents($pageID, $this->Page->getPageTypeID());

        // Get page comments
        $pageComments     = $this->Page->getPageComments($pageID);
        $pageCommentsList = array();

        $comment = new Comment();
        $commentTypeID = $comment->getCommentTypeID();

        // Check each comment for children, construct ordered list with levels
        for ($ac = 0; $ac < count($pageComments); $ac++) {
            $tid         = $pageComments[$ac]['commentID'];
            $thisComment = $comment->getBodyContents($tid, $commentTypeID, NULL, 1);
            if (!empty($thisComment[0]['bodyContentText'])) {
                $body = $thisComment[0]['bodyContentText'];
            } else {
                $body = "Empty......";
            }
            $commentDatePublished = $pageComments[$ac]['commentDatePublished'];
            $pageCommentsList[]   = array('level'                => 1,
                'commentID'            => $tid,
                'commentDatePublished' => $commentDatePublished,
                'commentBodyText'      => $body);

            // Nested loop
            $level = 1;
            while (count($as    = $this->Page->getPageComments($pageID, $tid)) > 0) {
                $level++;
                $tid                  = $as[0]['commentID'];
                $commentDatePublished = $as[0]['commentDatePublished'];
                $thisComment          = $comment->getBodyContents($tid, $commentTypeID, NULL, 1);
                if (!empty($thisComment[0]['bodyContentText'])) {
                    $body = $thisComment[0]['bodyContentText'];
                } else {
                    $body = "Empty......";
                }
                $pageCommentsList[] = array('level'                => $level,
                    'commentID'            => $tid,
                    'commentDatePublished' => $commentDatePublished,
                    'commentBodyText'      => $body);
            }
        }

        // Template data
        $this->set('page', $page);
        $this->set('pageBody', $pageBody);
        $this->set('pageComments', $pageCommentsList);
    }

}