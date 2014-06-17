<?php

class MenusController extends Controller
{
    public function __construct($controller, $model, $action, $template = NULL)
    {
        parent::__construct($controller, $model, $action, $template);
    }

    public static $staticData = array();

    /**
     * Controller specific input filtering on save, for use with StepFinish method
     * 
     * @param string $k
     * @param array|string $v
     * 
     * @return boolean True confirms match | False nothing was done
     */
    public static function createStepInput($k, $v)
    {
        // Menu links are in separate tables
        if (in_array($k, array('inp_menuLinkDisplay', 'inp_menuLinkAliasURL', 'inp_menuLinkActualPageID', 'inp_menuLinkActualURL'))) {
            self::$staticData[$k] = $v;
            return true;
        }
        return false;
    }

    /**
     * Controller specific finish Create step after first input save
     * 
     * @param string $id
     * 
     * @return boolean
     */
    public static function createStepFinish($id)
    {
        $Domain = new Domain();
        $MenuLink = new MenuLink();
        $RewriteMapping = new RewriteMapping();

        // Remove existing rewrite rules (across all selected brands domains)
        $Menu = new Menu();
        $m = $Menu->getSingle(array('menuID' => $id));
        $domains = $Domain->getWhere(array('brandID' => $m['brandID']));
        $existingMenuLinks = $MenuLink->getWhere(array('menuID' => $id));
        foreach ($domains as $domain)
        {
            $domainID = $domain['domainID'];
            $domainName = $domain['domainName'];
            foreach ($existingMenuLinks as $existingMenuLink)
            {
                $RewriteMapping->removeRewriteRule($existingMenuLink['url'], $domainName, $domainID);
            }
        }
        
        // Remove menulinks
        $MenuLink->delete(array('menuID' => $id));
        
        // Create menulinks with rewrite rules (across all selected brands domains)
        $data = array();
        for ($i = 0; $i < count(self::$staticData['inp_menuLinkDisplay']); $i++) {
            $display = self::$staticData['inp_menuLinkDisplay'][$i];
            $aliasURL = self::$staticData['inp_menuLinkAliasURL'][$i];
            $actualPageID = self::$staticData['inp_menuLinkActualPageID'][$i];
            $actualURL = self::$staticData['inp_menuLinkActualURL'][$i];
            
            // If selection is made and custom url is entered (must start with http), custom url will override selection.
            // otherwise the selected page id will be used
            if (!empty($actualURL) && preg_match('/^http/', $actualURL))
            {
                $actualPageID = NULL;
            }
            else
            {
                if (!empty($actualPageID))
                {
                    $actualURL = "/pages/read/".$actualPageID;
                }
            }
            
            // Skip empty/cloneable tr
            if (empty($display) && empty($aliasURL) && empty($actualURL)) {
                continue;
            }
            $data['menuID']       = $id;
            $data['sortOrder']    = $i;
            $data['parentLinkID'] = NULL;
            $data['display']      = $display;
            $data['url']          = $aliasURL;
            $data['linkActive']   = 1;
            $linkID = $MenuLink->update($data);
            
            // Rewrite rules for alias -> actual
            if (!empty($aliasURL)
                    && !empty($actualURL)
                    && $aliasURL != $actualURL)
            {
                foreach ($domains as $domain)
                {
                    $domainID = $domain['domainID'];
                    $rewriteRule = array('aliasURL' => $aliasURL,
                        'actualURL' => $actualURL,
                        'domainID' => $domainID);
                    $found = $RewriteMapping->getSingle($rewriteRule);
                    if (empty($found))
                    {
                        Errors::debugLogger(__METHOD__.': *** Create rewrite rule... '.$aliasURL.' => '.$actualURL);
                        $rewriteRule['sortOrder'] = 99;
                        $RewriteMapping->update($rewriteRule);
                    } else {
                        Errors::debugLogger(__METHOD__.': *** Already found:');
                        Errors::debugLogger($found);
                    }
                }
            }
        }
        return true;
    }

    /**
     * Controller specific finish Delete step after first input delete
     */
    public static function deleteStepFinish($args = NULL)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);
        $ID       = $args;
        
        // Remove existing rewrite rules (across all selected brands domains)
        $Menu = new Menu();
        $MenuLink = new MenuLink();
        $Domain = new Domain();
        $RewriteMapping = new RewriteMapping();
        $m = $Menu->getSingle(array('menuID' => $ID));
        $domains = $Domain->getWhere(array('brandID' => $m['brandID']));
        $existingMenuLinks = $MenuLink->getWhere(array('menuID' => $ID));
        foreach ($domains as $domain)
        {
            $domainID = $domain['domainID'];
            $domainName = $domain['domainName'];
            foreach ($existingMenuLinks as $existingMenuLink)
            {
                $RewriteMapping->removeRewriteRule($existingMenuLink['url'], $domainName, $domainID);
            }
        }
        
        // Remove all menu links
        $MenuLink->delete(array('menuID' => $ID));
    }

    /**
     * Pre-selects brand ID
     * 
     * @param int $ID
     * @param array $data
     */
    public function prepareFormCustom($ID = NULL, $data)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);
        
        // Get Menu of MenuLinkID
        $menuID = FALSE;
        if (!empty($data['inp_menuLinkID']))
        {
            $MenuLink = new MenuLink();
            $mr = $MenuLink->getSingle(array('menuLinkID' => $data['inp_menuLinkID']));
            if (!empty($mr))
            {
                $menuID = $mr['menuID'];
            }
        }
        
        parent::prepareForm($ID, $data['inp_brandID'], FALSE, FALSE, $menuID);
    }
    
    /**
     * Generates <option> list for Menu select list use in template
     */
    public function GetMenuChoiceList($SelectedID = FALSE)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);
        $menusList = $this->Menu->getAll();
        if (empty($menusList)) {
            $menuChoiceList = "<option value=''>-- None --</option>";
        } else {
            $menuChoiceList = "<option value=''>-- None --</option>";
            foreach ($menusList as $menu) {
                $selected = '';
                if ($SelectedID != FALSE && $SelectedID != "ALL" && $menu['menuID'] == $SelectedID) {
                    $selected = " selected";
                }
                $menuChoiceList .= "<option value='" . $menu['menuID'] . "'" . $selected . ">" . $menu['menuName'] . "</option>";
            }
        }
        return $menuChoiceList;
    }

}