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
        //Errors::debugLogger(__FILE__.'@'.__LINE__.' createStepInput...',10);
        // Page info and body are in separate tables
        // MenuID on creation to create menu link
        if (in_array($k, array('inp_pageBody', 'inp_menuID', 'inp_pageAlias'))) {
            //Errors::debugLogger(__FILE__."@".__LINE__." k:$k = v:$v",10);
            self::$staticData[$k] = $v;
            return true;
        }

        // We need page title, brandID if creating menu
        if (in_array($k, array('inp_pageName', 'inp_brandID'))) {
            //Errors::debugLogger(__FILE__."@".__LINE__." k:$k = v:$v",10);
            self::$staticData[$k] = $v;
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
        Errors::debugLogger(__FILE__.'@'.__LINE__.' createStepPreSave...',10);
        
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
    public static function createStepFinish($id, $data)
    {
        Errors::debugLogger(__FILE__.'@'.__LINE__.' createStepFinish...',10);
        
        // Save page body
        $Page          = new Page();
        $bodyType      = $Page->getPageTypeID();
        $bodyContentID = $Page->saveBodyContents($id, $bodyType, self::$staticData['inp_pageBody'], $_SESSION['user']['userID']);
        // Set previous bodies to inactive
        $Page->setBodyContentActive($id, $bodyType, $bodyContentID);

        // Create menu link?
        if (!empty(self::$staticData['inp_menuID'])
                && self::$staticData['inp_menuID'] != "NULL") {
            Errors::debugLogger("Create menu link...");
            $display = self::$staticData['inp_pageName'];
            $alias   = self::$staticData['inp_pageAlias'];
            $actualURL = '/pages/read/' . $id;
            if (empty($alias)) {
                $alias = '/' . str_replace(' ', '-', $display);
            } else {
                $alias = '/'.$alias;
            }
            // Menu Link == "Display" => "/AliasURL"
            $MenuLink   = new MenuLink();
            $menuLinkID = $MenuLink->update(array('menuID'     => self::$staticData['inp_menuID'],
                'sortOrder'  => 99,
                'display'    => $display,
                'url'        => $alias,
                'linkActive' => 1));
            // Every domain for this brand, alias mapping "/AliasURL" => "/real/url/123"
            $Domain  = new Domain();
            $domains = $Domain->getWhere(array('brandID' => self::$staticData['inp_brandID']));
            $reID    = NULL;
            if (!empty($domains)) {
                $RewriteMapping = new RewriteMapping();
                
                // @TODO check if mapping already exists?
                
                foreach ($domains as $domain) {
                    $reID = $RewriteMapping->update(array('aliasURL'  => $alias,
                        'actualURL' => $actualURL,
                        'sortOrder' => 99,
                        'domainID'  => $domain['domainID']));
                }
            }

        }
        return true;
    }

    /**
     * @param type $args
     */
    public function read($args)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);
        if (is_array($args)) {
            $ID = $args[0];
        } else {
            $ID = $args;
        }
        
        // Ensures page exists
        Errors::debugLogger("Ensures page exists...",10);
        parent::read($args);
        
        Errors::debugLogger("Past read...", 10);
        
        // ACL: Ensures user has permission to view requested page
        if (!empty($this->template->data['page']['pageRestricted']))
        {
            // Page is restricted
            if (empty($_SESSION['user_logged_in'])
                    || !in_array($_SESSION['user']['usergroup']['usergroupName'], array('Administrators', 'Store Owners')))
            {
                // Access Denied
                Errors::debugLogger("Access Denied, redirecting...", 10);
                unset($this->template->data['page']);
                $this->set('success', FALSE);
                header('Location: /' . $this->controller . '/readall');
            }
        }
    }
    
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