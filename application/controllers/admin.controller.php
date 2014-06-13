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
        $this->set('title', BRAND . ' ' . $_SESSION['user']['usergroup']['usergroupName']);
    }

}