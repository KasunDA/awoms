<?php

class AdminController extends Controller
{
    // Home
    public function home()
    {
        // ACL
        if (empty($_SESSION['user_logged_in']) || $_SESSION['user']['usergroup']['usergroupName'] != "Administrators") {
            header('Location: /users/login?returnURL=/admin/home');
            exit(0);
        }

        $this->set('title', BRAND . ' Administration');
    }

}