<?php

class AdminController extends Controller
{
    // Home
    public function home($args = NULL)
    {
        // ACL
        if (empty($_SESSION['user_logged_in'])) {
            header('Location: /users/login?returnURL=/owners');
            exit(0);
        }
        var_dump($_SESSION);
        if ($_SESSION['user']['usergroup']['usergroupName'] == "Administrators")
        {
            $this->set('title', BRAND . ' Administration');
        } else {
            $this->set('title', BRAND . ' Owners');
        }
    }

}