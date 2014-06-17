<?php

class Menu extends Model
{
    protected static function getMenuColumns()
    {
        $cols = array('menuID', 'brandID', 'menuName', 'menuActive');
        return $cols;
    }

    /**
     * Load additional model specific info when getWhere is called
     * 
     * @param type $item
     */
    public static function LoadExtendedItem($item)
    {
        // Load the menu's links
        $MenuLink      = new MenuLink();
        $item['links'] = $MenuLink->getWhere(array('menuID'     => $item['menuID'], 'linkActive' => 1));

        // Load the menu's brand
        $Brand         = new Brand();
        $item['brand'] = $Brand->getSingle(array('brandID'     => $item['brandID'], 'brandActive' => 1));
        
        // Foreach brands domain, check for rewrite rules:
        $Domain = new Domain();
        $domains = $Domain->getWhere(array('brandID' => $item['brandID']));
        $RewriteMapping = new RewriteMapping();
        foreach ($domains as $domain)
        {
            // Load rewrite map alias for menu links
            $i = 0;
            foreach ($item['links'] as $link)
            {
                $rewriteRule = $RewriteMapping->getSingle(array('domainID' => $domain['domainID'],
                    'aliasURL' => $link['url']));
                if (!empty($rewriteRule))
                {
                    $item['links'][$i]['actualURL'] = $rewriteRule['actualURL'];
                }
                $i++;
            }
        }

        /* */
        // Foreach menu link, build actual url selection list, if actualurl is set and matches, pre-select that item in list
        $Page = new Page();
        $_pages = $Page->getWhere(array('brandID' => $item['brandID'],
            'pageActive' => 1));
        
        // For clone row:
        $menuPageChoiceList = '';
        foreach ($_pages as $_page)
        {
            $menuPageChoiceList .= "<option value='".$_page['pageID']."'>".$_page['pageName']."</option>";
        }
        $item['pageChoiceList'] = $menuPageChoiceList;
        
        // For each menu link:
        $i = 0;
        foreach ($item['links'] as $link)
        {
            $pageChoiceList = '';
            foreach ($_pages as $_page)
            {
                $selected = "";
                if (!empty($link['actualURL']))
                {
                    if ($link['actualURL'] == "/pages/read/".$_page['pageID'])
                    {
                        $selected = " selected";
                    }
                }
                $pageChoiceList .= "<option value='".$_page['pageID']."'".$selected.">".$_page['pageName']."</option>";
            }
            $item['links'][$i]['pageChoiceList'] = $pageChoiceList;
            $i++;
        }
        
        return $item;
    }

    /**
     * Admin menu is hard-coded here, but based on ACL, this contstructs and returns the nested ul/li
     * 
     * @return string
     */
    private function admin()
    {
        /** Build Menu based on ACL; so that only menu items showing are those the user has access to * */
        $menu = array();

        // Brands
        if (ACL::IsUserAuthorized('brands', 'readall')) {
            $menu['Brands'] = array(
                "display" => "Brands",
                "url"     => "/brands/readall");
            if (ACL::IsUserAuthorized('brands', 'create')) {
                $menu['Brands']['sub']['Add Brand'] = array(
                    "display" => "Add Brand",
                    "url"     => "/brands/create");
            }
        }

        // Domains
        if (ACL::IsUserAuthorized('domains', 'readall')) {
            // Add to Brands sub-menu if can
            if (!empty($menu['Brands'])) {
                $menu['Brands']['sub']['Domains'] = array(
                    "display" => "Domains",
                    "url"     => "/domains/readall");
            } else {
                $menu['Domains'] = array(
                    "display" => "Domains",
                    "url"     => "/domains/readall");
            }

            if (ACL::IsUserAuthorized('domains', 'create')) {
                // Add to Brands sub-menu if can
                if (!empty($menu['Brands'])) {
                    $menu['Brands']['sub']['Domains']['sub']['Add Domain'] = array(
                        "display" => "Add Domain",
                        "url"     => "/domains/create");
                } else {
                    $menu['Domains']['sub']['Add Domain'] = array(
                        "display" => "Add Domain",
                        "url"     => "/domains/create");
                }
            }
        }

        // Menus
        if (ACL::IsUserAuthorized('menus', 'readall')) {
            // Add to Brands sub-menu if can
            if (!empty($menu['Brands'])) {
                $menu['Brands']['sub']['Menus'] = array(
                    "display" => "Menus",
                    "url"     => "/menus/readall");
            } else {
                $menu['Menus'] = array(
                    "display" => "Menus",
                    "url"     => "/menus/readall");
            }

            if (ACL::IsUserAuthorized('menus', 'create')) {
                // Add to Brands sub-menu if can
                if (!empty($menu['Brands'])) {
                    $menu['Brands']['sub']['Menus']['sub']['Add Menu'] = array(
                        "display" => "Add Menu",
                        "url"     => "/menus/create");
                } else {
                    $menu['Menus']['sub']['Add Menu'] = array(
                        "display" => "Add Menu",
                        "url"     => "/menus/create");
                }
            }
        }

        // Users
        if (ACL::IsUserAuthorized('users', 'readall')) {
            $menu['Users'] = array(
                "display" => "Users",
                "url"     => "/users/readall");

            if (ACL::IsUserAuthorized('users', 'create')) {
                $menu['Users']['sub']['Add User'] = array(
                    "display" => "Add User",
                    "url"     => "/users/create");
            }
        }

        // Usergroups
        if (ACL::IsUserAuthorized('usergroups', 'readall')) {
            // Add to Users sub-menu if can
            if (!empty($menu['Users'])) {
                $menu['Users']['sub']['Usergroups'] = array(
                    "display" => "Usergroups",
                    "url"     => "/usergroups/readall");
            } else {
                $menu['Usergroups'] = array(
                    "display" => "Usergroups",
                    "url"     => "/usergroups/readall");
            }

            if (ACL::IsUserAuthorized('usergroups', 'create')) {
                // Add to Users sub-menu if can
                if (!empty($menu['Users'])) {
                    $menu['Users']['sub']['Usergroups']['sub']['Add Usergroup'] = array(
                        "display" => "Add Usergroup",
                        "url"     => "/usergroups/create");
                } else {
                    $menu['Usergroups']['sub']['Add Usergroup'] = array(
                        "display" => "Add Usergroup",
                        "url"     => "/usergroups/create");
                }
            }
        }

        // Pages
        if (ACL::IsUserAuthorized('pages', 'readall')) {
            $menu['Pages'] = array(
                "display" => "Pages",
                "url"     => "/pages/readall");

            if (ACL::IsUserAuthorized('pages', 'create')) {
                $menu['Pages']['sub']['Add Page'] = array(
                    "display" => "Add Page",
                    "url"     => "/pages/create");
            }
        }

        // Articles
        if (ACL::IsUserAuthorized('articles', 'readall')) {
            $menu['Articles'] = array(
                "display" => "Articles",
                "url"     => "/articles/readall");

            if (ACL::IsUserAuthorized('articles', 'create')) {
                $menu['Articles']['sub']['Add Article'] = array(
                    "display" => "Add Article",
                    "url"     => "/articles/create");
            }
        }

        // Comments
        if (ACL::IsUserAuthorized('comments', 'readall')) {
            $menu['Comments'] = array(
                "display" => "Comments",
                "url"     => "/comments/readall");
        }

        $menu['Log Out'] = array(
            "display" => "Log Out",
            "url"     => "/users/logout");

        /** End construct menu based off ACL * */
        $finalMenu = self::buildMenu($menu);
        //$this->set('finalMenu', $finalMenu);
        return $finalMenu;
    }

    /**
     * User menu is stored in database, this contstructs and returns the nested ul/li
     * 
     * @return string
     */
    private function user($menuName, $ulClass, $menuTitle)
    {
        $res = self::getSingle(array('brandID' => $_SESSION['brandID'],
            'menuActive' => 1,
            'menuName' => $menuName));
        if (empty($res)) {
            return false;
        }
        $MenuLink     = new MenuLink();
        $res['links'] = $MenuLink->getWhere(array('menuID'     => $res['menuID'], 'linkActive' => 1));
        
        /** Build Menu **/
        $menu = array();
        foreach ($res['links'] as $menuLink) {
            if (empty($menuLink['parentLinkID'])) {   // Is Parent
                $menu[$menuLink['display']] = array(
                    "display" => $menuLink['display'],
                    "url"     => $menuLink['url']);
            } else {
                // Is Child; Find parent
                foreach ($res['links'] as $parentMenuLink) {
                    if ($parentMenuLink['linkID'] == $menuLink['parentLinkID']) {
                        $menu[$parentMenuLink['display']]['sub'][$menuLink['display']] = array(
                            "display" => $menuLink['display'],
                            "url"     => $menuLink['url']);
                    }
                }
            }
        }
        $finalMenu = self::buildMenu($menu, FALSE, $ulClass, $menuTitle);
        return $finalMenu;
    }

    /*
     * Recursive menu builder
     */
    private static function buildMenu($menu_array, $is_sub = FALSE, $ulClass = FALSE, $menuTitle = FALSE)
    {
        $menu = "\n<ul>\n"; // Open the menu container
        if ($ulClass !== FALSE) {
            $menu = "\n<ul class='" . $ulClass . "'>\n";
        }
        
        if (!empty($menuTitle))
        {
            $menu .= "<li class='heading'>".$menuTitle."</li>";
        }

        /*
         * Loop through the array to extract element values
         */
        foreach ($menu_array as $id => $properties) {

            /*
             * Because each page element is another array, we
             * need to loop again. This time, we save individual
             * array elements as variables, using the array key
             * as the variable name.
             */
            foreach ($properties as $key => $val) {

                /*
                 * If the array element contains another array,
                 * call the buildMenu() function recursively to
                 * build the sub-menu and store it in $sub
                 */
                if (is_array($val)) {
                    $sub = self::buildMenu($val, TRUE);
                }

                /*
                 * Otherwise, set $sub to NULL and store the
                 * element's value in a variable
                 */ else {
                    $sub  = NULL;
                    $$key = $val;
                }
            }

            /*
             * If no array element had the key 'url', set the
             * $url variable equal to the containing element's ID
             */
            if (!isset($url)) {
                $url = $id;
            }

            /*
             * Use the created variables to output HTML
             */

            /*
             * If the supplied array is part of a sub-menu, add the sub-menu class
             */
            $attr = "";
            if (!empty($sub)) {
                $attr = " class='has-sub'";
            }
            $menu .= "\t\t<li$attr>\n\t\t\t<a href='$url'>$display</a>$sub\n\t\t</li>\n";

            /*
             * Destroy the variables to ensure they're reset
             * on each iteration
             */
            unset($url, $display, $sub);
        }

        /*
         * Close the menu container and return the markup for output
         */
        return $menu . "</ul>\n";
    }

    /**
     * Returns dynamically built menu depending on user_logged_in status
     * 
     * @param string $menuName
     * @param string $ulClass
     * 
     * @return string
     */
    public function getMenu($menuName = NULL, $ulClass = NULL, $menuTitle = NULL)
    {
        if (!empty($_SESSION['user_logged_in'])) {
            return self::admin();
        }
        return self::user($menuName, $ulClass, $menuTitle);
    }

}