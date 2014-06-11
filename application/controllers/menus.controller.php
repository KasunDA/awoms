<?php

class MenusController extends Controller
{
    public function __construct($controller, $model, $action, $template = NULL)
    {
        parent::__construct($controller, $model, $action, $template);
    }


    /**
     * Create
     */
    public function create($args = NULL)
    {
        $this->set('title', 'Menus :: Create');

        // Step 1: Create/Update form
        if ($this->step == 1) {

//            // Prepare Create Form
//            // Restrict viewing list if non-admin
//            $brandsList = "ALL";
//            if ($_SESSION['user']['usergroup']['usergroupName'] != "Administrators") {
//                $brandsList = NULL;
//            }
//            parent::prepareForm(NULL, $brandsList);
        }

        // Step 2: Save page
        elseif ($this->step == 2) {

            // Data array to be passed to sql
            $data = array();

            // Gets input data from post, must begin with "inp_"
            foreach ($_POST as $k => $v) {
                if (!preg_match('/^inp_(.*)/', $k, $m)) {
                    continue;
                }

                // Menu links are in separate tables
                if ($k == 'inp_menuLinkDisplay') {
                    $inp_menuLinkDisplay = $v;
                    continue;
                }
                if ($k == 'inp_menuLinkURL') {
                    $inp_menuLinkURL = $v;
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

                // Menu id (new or existing)
                if ($k == 'inp_menuID') {
                    $inp_menuID = $v;
                }

                // Menu info col/data
                $data[$m[1]] = $v;
            }

            // Save menu info, getting ID
            $menuID = $this->Menu->saveMenuInfo($data);
            if ($inp_menuID != 'DEFAULT') {
                // Updated (existing ID)
                $menuID = $inp_menuID;
            }

            $this->set('menuID', $menuID);

            /* Save menu Links */
            $MenuLink = new MenuLink();

            // First remove all links then save new list:
            $MenuLink->removeAllMenuLinks($menuID);

            $data = array();

            for ($i = 0; $i < count($inp_menuLinkDisplay); $i++) {
                $display = $inp_menuLinkDisplay[$i];
                $url     = $inp_menuLinkURL[$i];

                // Skip empty/cloneable tr
                if (empty($display) && empty($url)) {
                    continue;
                }

                $data['menuID']       = $menuID;
                $data['sortOrder']    = $i;
                $data['parentLinkID'] = NULL;
                $data['display']      = $display;
                $data['url']          = $url;
                $data['linkActive']   = 1;

                $linkID = $MenuLink->update($data);
            }

            // @TODO if this menu being activated, ensure other menus are deactivated (for matching brand)
            // Final success status
            $this->set('success', TRUE);
        }

        // Restrict viewing list if non-admin
        $limit = NULL;
        if ($_SESSION['user']['usergroup']['usergroupName'] != "Administrators") {
            $limit = "brandID=" . $_SESSION['brandID'];
        }

        // Get updated menu list
        $this->set('menus', $this->getMenus($limit));
    }

    /**
     * Update
     */
    public function update($args)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);
        parent::update($args);

        $this->set('title', 'Menus :: Update');
        $res = TRUE;
        if ($this->step == 1) {

            // Gets input data from post, must begin with "inp_"
            foreach ($menuInfo as $k => $v) {
                if ($k == "brandID") {
                    $selectedBrandID = $v;
                }
                $this->set("inp_" . $k, $v);
            }

            // Prepare Create Form
            // Restrict viewing list if non-admin
            $limit = NULL;
            if ($_SESSION['user']['usergroup']['usergroupName'] != "Administrators") {
                $selectedBrandID = NULL;
                $limit           = "brandID=" . $_SESSION['brandID'];
            }

            // Get updated menu list
            $this->set('menus', $this->getMenus($limit));

            parent::prepareForm($ID, $selectedBrandID);
        } elseif ($this->step == 2) {
            // Use create method to update existing
            $res = $this->create();
        }

        return $res;
    }

    /**
     * Delete
     */
    public function delete($args)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);
        parent::delete($args);

        $this->set('title', 'Menus :: Delete');

        if ($this->step == 1) {

            header('Location: /menus/readall');
            exit(0);
        } elseif ($this->step == 2) {

            if (empty($_POST['inp_menuID'])) {
                $this->set('success', FALSE);
                return;
            }

            $ID       = $_POST['inp_menuID'];
            $MenuLink = new MenuLink();
            $MenuLink->delete(array('menuID' => $ID));
            $this->Menu->delete(array('menuID' => $ID));

            // Restrict viewing list if non-admin
            $limit      = NULL;
            $formBrands = "ALL";
            if ($_SESSION['user']['usergroup']['usergroupName'] != "Administrators") {
                $limit      = "brandID=" . $_SESSION['brandID'];
                $formBrands = NULL;
            }

            // Get updated menu list
            $this->set('menus', $this->getMenus($limit));

            parent::prepareForm($ID, $formBrands);

            $this->set('success', TRUE);
        }
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
        $menuIDs = $this->Menu->getMenuIDs($where);
        $menus   = array();
        foreach ($menuIDs as $b) {
            $menus[] = $this->Menu->getMenuInfo($b['menuID']);
        }
        return $menus;
    }

    /**
     * Admin menu is hard-coded here, but based on ACL, this contstructs and returns the nested ul/li
     * 
     * @return string
     */
    public function admin()
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
    public function user()
    {
        /** Build Menu based on ACL; so that only menu items showing are those the user has access to * */
        $menuLinks = $this->Menu->getBrandActiveMenu();
        if (empty($menuLinks)) return false;

        $menu = array();
        foreach ($menuLinks['links'] as $menuLink) {
            if (empty($menuLink['parentLinkID'])) {   // Is Parent
                $menu[$menuLink['display']] = array(
                    "display" => $menuLink['display'],
                    "url"     => $menuLink['url']);
            } else {
                // Is Child; Find parent
                foreach ($menuLinks['links'] as $parentMenuLink) {
                    if ($parentMenuLink['linkID'] == $menuLink['parentLinkID']) {
                        $menu[$parentMenuLink['display']]['sub'][$menuLink['display']] = array(
                            "display" => $menuLink['display'],
                            "url"     => $menuLink['url']);
                    }
                }
            }
        }
        /** End construct menu based off ACL * */
        $ulClass   = "menu_horizontal menu_header menu_hover";
        $finalMenu = self::buildMenu($menu, FALSE, $ulClass);
        //$this->set('finalMenu', $finalMenu);
        return $finalMenu;
    }

    /*
     * Recursive menu builder
     */
    public static function buildMenu($menu_array, $is_sub = FALSE, $ulClass = FALSE)
    {
        $menu = "\n<ul>\n"; // Open the menu container
        if ($ulClass !== FALSE) {
            $menu = "\n<ul class='" . $ulClass . "'>\n";
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

}