<?php

class Menu extends Model
{

    protected static function getMenuColumns()
    {
        $cols = array('menuID', 'brandID', 'menuType', 'menuName', 'menuTitle', 'menuRestricted', 'menuActive');
        return $cols;
    }

    public function getWhere($where = NULL, $cols = NULL, $order = NULL, $aclWhere = NULL, $in = NULL, $loadChildren = FALSE)
    {
        if ($order == NULL)
        {
            $order = "brandID, menuType, menuName";
        }
        return parent::getWhere($where, $cols, $order, $aclWhere, $in, $loadChildren);
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

        // Foreach brands domain, check for rewrite rules:
        $Domain  = new Domain();
        $domains = $Domain->getWhere(array('brandID' => $item['brandID']), NULL, NULL, NULL, NULL, TRUE); // Load Children=TRUE
        foreach ($domains as $domain)
        {
            // Load rewrite map alias for menu links
            $i = 0;
            foreach ($item['links'] as $link)
            {
                // Domain->RewriteRules
                foreach ($domain['rewriteRules'] as $rw)
                {
                    if ($rw['aliasURL'] == $link['url'])
                    {
                        $item['links'][$i]['actualURL'] = $rw['actualURL'];
                        break;
                    }
                }
                $i++;
            }
        }

        /* */
        // Foreach menu link, build actual url selection list, if actualurl is set and matches, pre-select that item in list
        $Page   = new Page();
        $_pages = $Page->getWhere(array('brandID'    => $item['brandID'],
            'pageActive' => 1));

        // For clone row:
        $menuPageChoiceList = '';
        foreach ($_pages as $_page)
        {
            $menuPageChoiceList .= "<option value='" . $_page['pageID'] . "'>" . $_page['pagePrivateName'] . "</option>";
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
                    if ($link['actualURL'] == "/pages/read/" . $_page['pageID'])
                    {
                        $selected = " selected";
                    }
                }
                $pageChoiceList .= "<option value='" . $_page['pageID'] . "'" . $selected . ">" . $_page['pagePrivateName'] . "</option>";
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
        if (ACL::IsUserAuthorized('brands', 'readall'))
        {
            $menu['Brands'] = array(
                "display" => "Brands",
                "url"     => "/brands/readall");
            if (ACL::IsUserAuthorized('brands', 'create'))
            {
                $menu['Brands']['sub']['Add Brand'] = array(
                    "display" => "Add Brand",
                    "url"     => "/brands/create");
            }
        }

        // Files
        if (ACL::IsUserAuthorized('files', 'readall'))
        {
            $menu['Files'] = array(
                "display" => "Files",
                "url"     => "/files/readall");
            // @TODO
            //if (ACL::IsUserAuthorized('files', 'create'))
            //{
                //$menu['Files']['sub']['Add File'] = array(
                    //"display" => "Add File",
                    //"url"     => "/files/create");
            //}
        }

        // Domains
        if (ACL::IsUserAuthorized('domains', 'readall'))
        {
            // Add to Brands sub-menu if can
            if (!empty($menu['Brands']))
            {
                $menu['Brands']['sub']['Domains'] = array(
                    "display" => "Domains",
                    "url"     => "/domains/readall");
            }
            else
            {
                $menu['Domains'] = array(
                    "display" => "Domains",
                    "url"     => "/domains/readall");
            }

            if (ACL::IsUserAuthorized('domains', 'create'))
            {
                // Add to Brands sub-menu if can
                if (!empty($menu['Brands']))
                {
                    $menu['Brands']['sub']['Domains']['sub']['Add Domain'] = array(
                        "display" => "Add Domain",
                        "url"     => "/domains/create");
                }
                else
                {
                    $menu['Domains']['sub']['Add Domain'] = array(
                        "display" => "Add Domain",
                        "url"     => "/domains/create");
                }
            }
        }

        // Menus
        if (ACL::IsUserAuthorized('menus', 'readall'))
        {
            // Add to Brands sub-menu if can
            if (!empty($menu['Brands']))
            {
                $menu['Brands']['sub']['Menus'] = array(
                    "display" => "Menus",
                    "url"     => "/menus/readall");
            }
            else
            {
                $menu['Menus'] = array(
                    "display" => "Menus",
                    "url"     => "/menus/readall");
            }

            if (ACL::IsUserAuthorized('menus', 'create'))
            {
                // Add to Brands sub-menu if can
                if (!empty($menu['Brands']))
                {
                    $menu['Brands']['sub']['Menus']['sub']['Add Menu'] = array(
                        "display" => "Add Menu",
                        "url"     => "/menus/create");
                }
                else
                {
                    $menu['Menus']['sub']['Add Menu'] = array(
                        "display" => "Add Menu",
                        "url"     => "/menus/create");
                }
            }
        }

        // Services
        if (ACL::IsUserAuthorized('services', 'readall'))
        {
            // Add to Brands sub-menu if can
            if (!empty($menu['Brands']))
            {
                $menu['Brands']['sub']['Services'] = array(
                    "display" => "Services",
                    "url"     => "/services/readall");
            }
            else
            {
                $menu['Services'] = array(
                    "display" => "Services",
                    "url"     => "/services/readall");
            }

            if (ACL::IsUserAuthorized('services', 'create'))
            {
                // Add to Brands sub-menu if can
                if (!empty($menu['Brands']))
                {
                    $menu['Brands']['sub']['Services']['sub']['Add Service'] = array(
                        "display" => "Add Service",
                        "url"     => "/services/create");
                }
                else
                {
                    $menu['Services']['sub']['Add Service'] = array(
                        "display" => "Add Service",
                        "url"     => "/services/create");
                }
            }
        }

        // Stores
        if (ACL::IsUserAuthorized('stores', 'readall'))
        {
            $menu['Stores'] = array(
                "display" => "Stores",
                "url"     => "/stores/readall");
            if (ACL::IsUserAuthorized('stores', 'create'))
            {
                $menu['Stores']['sub']['Add Store'] = array(
                    "display" => "Add Store",
                    "url"     => "/stores/create");
            }
        }
        
        // Carts
        if (ACL::IsUserAuthorized('carts', 'readall'))
        {
            $menu['Carts'] = array(
                "display" => "Carts",
                "url"     => "/carts/readall");
            if (ACL::IsUserAuthorized('carts', 'create'))
            {
                $menu['Carts']['sub']['Add Cart'] = array(
                    "display" => "Add Cart",
                    "url"     => "/carts/create");
            }
        }

        // Users
        if (ACL::IsUserAuthorized('users', 'readall'))
        {
            $menu['Users'] = array(
                "display" => "Users",
                "url"     => "/users/readall");

            if (ACL::IsUserAuthorized('users', 'create'))
            {
                $menu['Users']['sub']['Add User'] = array(
                    "display" => "Add User",
                    "url"     => "/users/create");
            }
        }

        // Usergroups
        /*
        if (ACL::IsUserAuthorized('usergroups', 'readall'))
        {
            // Add to Users sub-menu if can
            if (!empty($menu['Users']))
            {
                $menu['Users']['sub']['Usergroups'] = array(
                    "display" => "Usergroups",
                    "url"     => "/usergroups/readall");
            }
            else
            {
                $menu['Usergroups'] = array(
                    "display" => "Usergroups",
                    "url"     => "/usergroups/readall");
            }

            if (ACL::IsUserAuthorized('usergroups', 'create'))
            {
                // Add to Users sub-menu if can
                if (!empty($menu['Users']))
                {
                    $menu['Users']['sub']['Usergroups']['sub']['Add Usergroup'] = array(
                        "display" => "Add Usergroup",
                        "url"     => "/usergroups/create");
                }
                else
                {
                    $menu['Usergroups']['sub']['Add Usergroup'] = array(
                        "display" => "Add Usergroup",
                        "url"     => "/usergroups/create");
                }
            }
        }
         *
         */

        // Pages
        if (ACL::IsUserAuthorized('pages', 'readall'))
        {
            $menu['Pages'] = array(
                "display" => "Pages",
                "url"     => "/pages/readall");

            if (ACL::IsUserAuthorized('pages', 'create'))
            {
                $menu['Pages']['sub']['Add Page'] = array(
                    "display" => "Add Page",
                    "url"     => "/pages/create");
            }
        }

        // Articles
//        if (ACL::IsUserAuthorized('articles', 'readall')) {
//            $menu['Articles'] = array(
//                "display" => "Articles",
//                "url"     => "/articles/readall");
//
//            if (ACL::IsUserAuthorized('articles', 'create')) {
//                $menu['Articles']['sub']['Add Article'] = array(
//                    "display" => "Add Article",
//                    "url"     => "/articles/create");
//            }
//        }


        /*
        // Comments
        if (ACL::IsUserAuthorized('comments', 'readall'))
        {
            $menu['Comments'] = array(
                "display" => "Comments",
                "url"     => "/comments/readall");
        }
        */

        /*
        $menu['Log Out'] = array(
            "display" => "Log Out",
            "url"     => "/users/logout");
         */

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
    private function user($data, $ulClass)
    {
        $res = self::getSingle($data, NULL, TRUE); // TRUE loads menu links
        if (empty($res))
        {
            return false;
        }

        /** Build Menu * */
        $menu = array();
        foreach ($res['links'] as $menuLink)
        {
            if (empty($menuLink['parentLinkID']))
            {   // Is Parent
                $menu[$menuLink['display']] = array(
                    "display" => $menuLink['display'],
                    "url"     => $menuLink['url']);
            }
            else
            {
                // Is Child; Find parent
                foreach ($res['links'] as $parentMenuLink)
                {
                    if ($parentMenuLink['linkID'] == $menuLink['parentLinkID'])
                    {
                        $menu[$parentMenuLink['display']]['sub'][$menuLink['display']] = array(
                            "display" => $menuLink['display'],
                            "url"     => $menuLink['url']);
                    }
                }
            }
        }

        $finalMenu = self::buildMenu($menu, $res['menuTitle'], FALSE, $ulClass);
        return $finalMenu;
    }

    /*
     * Recursive menu builder (no touchy)
     */

    private static function buildMenu($menu_array, $menuTitle = FALSE, $is_sub = FALSE, $ulClass = FALSE)
    {
        $menu = "\n<ul>\n"; // Open the menu container
        if ($ulClass !== FALSE)
        {
            $menu = "\n<ul class='" . $ulClass . "'>\n";
        }

        if (!empty($menuTitle))
        {
            $menu .= "<li class='heading'>" . $menuTitle . "</li>";
        }

        /*
         * Loop through the array to extract element values
         */
        foreach ($menu_array as $id => $properties)
        {

            /*
             * Because each page element is another array, we
             * need to loop again. This time, we save individual
             * array elements as variables, using the array key
             * as the variable name.
             */
            foreach ($properties as $key => $val)
            {

                /*
                 * If the array element contains another array,
                 * call the buildMenu() function recursively to
                 * build the sub-menu and store it in $sub
                 */
                if (is_array($val))
                {
                    $sub = self::buildMenu($val, FALSE, TRUE);
                }

                /*
                 * Otherwise, set $sub to NULL and store the
                 * element's value in a variable
                 */
                else
                {
                    $sub  = NULL;
                    $$key = $val;
                }
            }

            /*
             * If no array element had the key 'url', set the
             * $url variable equal to the containing element's ID
             */
            if (!isset($url))
            {
                $url = $id;
            }

            /*
             * Use the created variables to output HTML
             */

            /*
             * If the supplied array is part of a sub-menu, add the sub-menu class
             */
            $attr = "";
            if (!empty($sub))
            {
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
    public function getMenu($menuType, $menuName = NULL, $ulClass = NULL, $loginRestricted = FALSE)
    {
        // Admin
        if (!empty($_SESSION['user_logged_in'])
                && $_SESSION['user']['usergroup']['usergroupName'] == "Administrators")
        {
            if ($menuType == "Heading Nav")
            {
                return self::admin();
            }
        }

        // Get where...
        $data               = array();
        $data['brandID']    = $_SESSION['brandID'];
        $data['menuType']   = $menuType;
        $data['menuActive'] = 1;

        if (!empty($menuName))
        {
            $data['menuName'] = $menuName;
        }

        // If user is logged in, default to restricted menu type
        $data['menuRestricted'] = 0;
        if (!empty($_SESSION['user_logged_in']))
        {
            $data['menuRestricted'] = 1;
        }
        $res = self::user($data, $ulClass);
        if (empty($res))
        {
            // If no menu found, try for nonrestricted menus, return empty if still nothing found
            $data['menuRestricted'] = 0;
            $res = self::user($data, $ulClass);
        }
        return $res;
    }

}