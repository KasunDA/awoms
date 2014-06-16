<?php

class InstallController extends Controller
{
    public function wizard()
    {
        // No wizard if already setup
        $Brand = new Brand();
        if (!empty($Brand->select("*", "brandActive=1"))) {
            header('Location: /admin/home');
            exit(0);
        }

        if (!empty($_POST['step'])) {

            // Parent Item Types
            $DB    = new Database();
            $query = "
                INSERT INTO `refParentItemTypes`
                (`parentTypeLabel`)
                VALUES
                ('User'),
                ('Page'),
                ('Article'),
                ('Comment')";
            $DB->query($query);

            // Brand
            $Brand                = new Brand();
            $brand['brandID']     = 1;
            $brand['brandName']   = $_POST['inp_brandName'];
            $brand['brandActive'] = 1;
            $brand['brandLabel']  = $_POST['inp_brandLabel'];
            $brand['activeTheme'] = $_POST['inp_activeTheme'];
            $Brand->update($brand);
            // Create brands usergroups
            BrandsController::createStepFinish($brand['brandID']);

            // User
            $User                = new User();
            $user['userID']      = 1;
            $user['usergroupID'] = 1;
            $user['userActive']  = 1;
            $user['userName']    = $_POST['inp_adminUsername'];
            $user['passphrase']  = $_POST['inp_adminPassphrase'];
            $user['userEmail']   = $_POST['inp_adminEmail'];
            $User->update($user);

            // Domain
            $Domain                 = new Domain();
            $domain['domainID']     = 1;
            $domain['brandID']      = $brand['brandID'];
            $domain['domainName']   = $_POST['inp_domainName'];
            $domain['domainActive'] = 1;
            $Domain->update($domain);

            // Menu
            $Menu               = new Menu();
            $menu['brandID']    = $brand['brandID'];
            $menu['menuName']   = 'Default Menu';
            $menu['menuActive'] = 1;
            $Menu->update($menu);
            
            // MenuLinks
            $MenuLink = new MenuLink();
            $menulink['menuID'] = 1;
            $menulink['sortOrder'] = 1;
            $menulink['display'] = 'Home';
            $menulink['url'] = '/';
            $menulink['linkActive'] = 1;
            $MenuLink->update($menulink);
            
            $menulink['menuID'] = 1;
            $menulink['sortOrder'] = 2;
            $menulink['display'] = 'Log In';
            $menulink['url'] = '/users/login';
            $menulink['linkActive'] = 1;
            $MenuLink->update($menulink);
        }
    }

}