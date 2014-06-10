<?php

class MenusController extends Controller
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
        $this->set('title', 'Menus :: View All');

        // Restrict viewing list if non-admin
        $limit = NULL;
        if ($_SESSION['user']['usergroup']['usergroupName'] != "Administrators")
        {
            $limit = "menuActive=1 AND brandID=".$_SESSION['brandID'];
        }
        
        // Get brand list
        $this->set('menus', $this->getMenus($limit));

        // Prepare Create Form
        parent::prepareForm();
    }
    
    /**
     * Edit
     */
    public function edit()
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);

        $this->set('title', 'Menus :: Edit');

        $args = func_get_args();
        if (!empty($args)) {
            $ID = $args[0];
            $this->set('menuID', $ID);
        }

        $res = TRUE;
        if ($this->step == 1) {
            $menu = $this->Menu->getMenuInfo($ID);
            $this->set('menu', $menu);

            // Get brand list
            $this->set('menus', $this->getMenus());

            // Gets input data from post, must begin with "inp_"
            foreach ($menu as $k => $v) {
                $this->set("inp_" . $k, $v);
            }

            // Prepare Create Form
            parent::prepareForm($ID);
        } elseif ($this->step == 2) {
            // Use create method to edit existing
            $res = $this->create();
        }

        return $res;
    }
    
    /**
     * Get Menu List
     * 
     * Returns array of all active menus and their links
     * 
     * @return array
     */
    public function getMenus($where = NULL)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);        
        if ($where == NULL) { $where = 'menuActive=1'; }
        $menuIDs = $this->Menu->getMenuIDs($where);
        $menus   = array();
        foreach ($menuIDs as $b) {
            $menu    = $this->Menu->getMenu($b['menuID']);
            $menus[] = $menu;
        }
        return $menus;
    }
    
    // Admin
    public function admin()
    {
        /** Build Menu based on ACL; so that only menu items showing are those the user has access to **/
        $menu = array();

        // Brands
        if (ACL::IsUserAuthorized('brands', 'viewall'))
        {
            $menu['Brands'] = array(
                "display" => "Brands",
                "url" => "/brands/viewall");
            if (ACL::IsUserAuthorized('brands', 'create'))
            {
                $menu['Brands']['sub']['Add Brand'] = array(
                    "display" => "Add Brand",
                    "url" => "/brands/create");
            }
        }

        // Domains
        if (ACL::IsUserAuthorized('domains', 'viewall'))
        {
            // Add to Brands sub-menu if can
            if (!empty($menu['Brands']))
            {
                $menu['Brands']['sub']['Domains'] = array(
                    "display" => "Domains",
                    "url" => "/domains/viewall");
            } else {
                $menu['Domains'] = array(
                    "display" => "Domains",
                    "url" => "/domains/viewall");
            }

            if (ACL::IsUserAuthorized('domains', 'create'))
            {
                // Add to Brands sub-menu if can
                if (!empty($menu['Brands']))
                {
                    $menu['Brands']['sub']['Domains']['sub']['Add Domain'] = array(
                        "display" => "Add Domain",
                        "url" => "/domains/create");
                } else {
                    $menu['Domains']['sub']['Add Domain'] = array(
                        "display" => "Add Domain",
                        "url" => "/domains/create");
                }
            }
        }
        
        // Menus
        // @TODO
        /*
        if (ACL::IsUserAuthorized('menus', 'viewall'))
        {
            // Add to Brands sub-menu if can
            if (!empty($menu['Brands']))
            {
                $menu['Brands']['sub']['Menus'] = array(
                    "display" => "Menus",
                    "url" => "/menus/viewall");
            } else {
                $menu['Menus'] = array(
                    "display" => "Menus",
                    "url" => "/menus/viewall");
            }

            if (ACL::IsUserAuthorized('menus', 'create'))
            {
                // Add to Brands sub-menu if can
                if (!empty($menu['Brands']))
                {
                    $menu['Brands']['sub']['Menus']['sub']['Add Menu'] = array(
                        "display" => "Add Menu",
                        "url" => "/menus/create");
                } else {
                    $menu['Menus']['sub']['Add Menu'] = array(
                        "display" => "Add Menu",
                        "url" => "/menus/create");
                }
            }
        }
         * 
         */

        // Users
        if (ACL::IsUserAuthorized('users', 'viewall'))
        {
            $menu['Users'] = array(
                "display" => "Users",
                "url" => "/users/viewall");

            if (ACL::IsUserAuthorized('users', 'create'))
            {
                $menu['Users']['sub']['Add User'] = array(
                    "display" => "Add User",
                    "url" => "/users/create");
            }
        }

        // Usergroups
        if (ACL::IsUserAuthorized('usergroups', 'viewall'))
        {
            // Add to Users sub-menu if can
            if (!empty($menu['Users']))
            {
                $menu['Users']['sub']['Usergroups'] = array(
                    "display" => "Usergroups",
                    "url" => "/usergroups/viewall");
            } else {
                $menu['Usergroups'] = array(
                    "display" => "Usergroups",
                    "url" => "/usergroups/viewall");
            }

            if (ACL::IsUserAuthorized('usergroups', 'create'))
            {
                // Add to Users sub-menu if can
                if (!empty($menu['Users']))
                {
                    $menu['Users']['sub']['Usergroups']['sub']['Add Usergroup'] = array(
                        "display" => "Add Usergroup",
                        "url" => "/usergroups/create");
                } else {
                    $menu['Usergroups']['sub']['Add Usergroup'] = array(
                        "display" => "Add Usergroup",
                        "url" => "/usergroups/create");
                }
            }
        }

        // Pages
        if (ACL::IsUserAuthorized('pages', 'viewall'))
        {
            $menu['Pages'] = array(
                "display" => "Pages",
                "url" => "/pages/viewall");

            if (ACL::IsUserAuthorized('pages', 'create'))
            {
                $menu['Pages']['sub']['Add Page'] = array(
                    "display" => "Add Page",
                    "url" => "/pages/create");
            }
        }

        // Articles
        if (ACL::IsUserAuthorized('articles', 'viewall'))
        {
            $menu['Articles'] = array(
                "display" => "Articles",
                "url" => "/articles/viewall");

            if (ACL::IsUserAuthorized('articles', 'create'))
            {
                $menu['Articles']['sub']['Add Article'] = array(
                    "display" => "Add Article",
                    "url" => "/articles/create");
            }
        }

        // Comments
        if (ACL::IsUserAuthorized('comments', 'viewall'))
        {
            $menu['Comments'] = array(
                "display" => "Comments",
                "url" => "/comments/viewall");
        }
        
        $menu['Log Out'] = array(
                "display" => "Log Out",
                "url" => "/users/logout");
        
        /** End construct menu based off ACL **/

        $finalMenu = self::buildMenu($menu);
        //$this->set('finalMenu', $finalMenu);
        return $finalMenu;
    }

    // User
    public function user()
    {
        /** Build Menu based on ACL; so that only menu items showing are those the user has access to **/
        $menuLinks = $this->Menu->getBrandActiveMenu();
        $menu = array();
        
        foreach ($menuLinks as $menuLink)
        {
            if (empty($menuLink['parentLinkID']))
            {
                // Is Parent
                $menu[$menuLink['display']] = array(
                "display" => $menuLink['display'],
                "url" => $menuLink['url']);
            } else {
                // Is Child; Find parent
                foreach ($menuLinks as $parentMenuLink)
                {
                    if ($parentMenuLink['linkID'] == $menuLink['parentLinkID'])
                    {
                        $menu[$parentMenuLink['display']]['sub'][$menuLink['display']] = array(
                            "display" => $menuLink['display'],
                            "url" => $menuLink['url']);
                    }
                }
            }
        }
        /** End construct menu based off ACL **/

        $ulClass = "menu_horizontal menu_header menu_hover";
        
        $finalMenu = self::buildMenu($menu, FALSE, $ulClass);
        //$this->set('finalMenu', $finalMenu);
        return $finalMenu;
    }
    
   /*
    * Recursive menu builder
    */
   public static function buildMenu($menu_array, $is_sub=FALSE, $ulClass=FALSE)
   {
       $menu = "\n<ul>\n"; // Open the menu container
       if ($ulClass !== FALSE)
       {
           $menu = "\n<ul class='".$ulClass."'>\n";
       }

       /*
        * Loop through the array to extract element values
        */
       foreach($menu_array as $id => $properties) {

           /*
            * Because each page element is another array, we
            * need to loop again. This time, we save individual
            * array elements as variables, using the array key
            * as the variable name.
            */
           foreach($properties as $key => $val) {

               /*
                * If the array element contains another array,
                * call the buildMenu() function recursively to
                * build the sub-menu and store it in $sub
                */
               if(is_array($val))
               {
                   $sub = self::buildMenu($val, TRUE);
               }

               /*
                * Otherwise, set $sub to NULL and store the
                * element's value in a variable
                */
               else
               {
                   $sub = NULL;
                   $$key = $val;
               }
           }

           /*
            * If no array element had the key 'url', set the
            * $url variable equal to the containing element's ID
            */
           if(!isset($url)) {
               $url = $id;
           }

           /*
            * Use the created variables to output HTML
            */

           /*
            * If the supplied array is part of a sub-menu, add the sub-menu class
            */
           $attr = "";
           if (!empty($sub)) { $attr = " class='has-sub'"; }
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
}