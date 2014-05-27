<?php

class UsersController extends Controller
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
        Errors::debugLogger(10, __METHOD__ . '@' . __LINE__);

        // Template data
        $this->set('title', 'Users :: View All');
        
        // Get users list
        $this->set('users', $this->getUsers());

        // Prepare Create Form
        parent::prepareForm(NULL, NULL, NULL, "ALL");
    }

    /**
     * Create
     */
    public function create()
    {
        Errors::debugLogger(10, __METHOD__ . '@' . __LINE__);
        
        // Get step or assume 1st step
        empty($_REQUEST['step']) ? $step = 1 : $step = $_REQUEST['step'];
        $this->set('step', $step);
        $this->set('title', 'Users :: Create');

        // Step 1: Create/Edit form
        if ($step == 1) {

            // Prepare Create Form
            parent::prepareForm(NULL, NULL, NULL, "ALL");
        }
        
        // Step 2: Save user
        elseif ($step == 2) {

            // Data array to be passed to sql
            $data = array();
            $data['userActive'] = 1;
            
            // Gets input data from post, must begin with "inp_"
            foreach ($_POST as $k => $v) {
                if (!preg_match('/^inp_(.*)/', $k, $m)) {
                    continue;
                }

                $this->set($k, $v);

                // User id (new or existing)
                if ($k == 'inp_userID') {
                    $inp_userID = $v;
                }

                // User info col/data
                $data[$m[1]] = $v;
            }

            // Save user info, getting ID
            $userID = $this->User->update($data);
            if ($inp_userID != 'DEFAULT') {
                // Updated (existing ID)
                $userID = $inp_userID;
            }
            $this->set('userID', $userID);
        }
        
        // Get updated list
        $this->set('users', $this->getUsers());
    }

    /**
     * Edit
     */
    public function edit()
    {
        Errors::debugLogger(10, __METHOD__ . '@' . __LINE__);

        // Get step or assume 1st step
        empty($_REQUEST['step']) ? $step = 1 : $step = $_REQUEST['step'];
        $this->set('step', $step);
        $this->set('title', 'Users :: Edit');

        $args = func_get_args();
        if (!empty($args)) {
            $ID = $args[0];
            $this->set('userID', $ID);
        }

        $res = TRUE;
        if ($step == 1) {
            $user = $this->User->getUserInfo($ID);
            $this->set('user', $user);

            // Get user list
            $this->set('users', $this->getUsers());

            // Gets input data from post, must begin with "inp_" 
            foreach ($user as $k => $v) {
                if ($k == "usergroupID") {
                    $selectedUsergroupID = $v;
                }
                $this->set("inp_" . $k, $v);
            }

            // Prepare Create Form
            parent::prepareForm($ID, NULL, NULL, $selectedUsergroupID);
        } elseif ($step == 2) {
            // Use create method to edit existing
            $res = $this->create();
        }

        return $res;
    }
    
    /**
     * Get User List
     * 
     * Returns array of all active users and their info
     * 
     * @return array
     */
    public function getUsers()
    {
        Errors::debugLogger(10, __METHOD__ . '@' . __LINE__);
        $userIDs = $this->User->getUserIDs('userActive=1');
        $users   = array();
        foreach ($userIDs as $ug) {
            $user    = $this->User->getUserInfo($ug['userID']);
            $users[] = $user;
        }
        return $users;
    }

    /**
     * Generates <option> list for User select list use in template
     */
    public function GetUserChoiceList($SelectedID = FALSE)
    {
        Errors::debugLogger(10, __METHOD__ . '@' . __LINE__);
        $usersList = $this->getUsers();
        if (empty($usersList)) {
            $userChoiceList = "<option value=''>--None--</option>";
        } else {
            $userChoiceList = '';
            foreach ($usersList as $user) {
                $selected = '';
                if ($SelectedID != FALSE && $SelectedID != "ALL" && $user['userID'] == $SelectedID) {
                    $selected = " selected";
                }
                $userChoiceList .= "<option value='" . $user['userID'] . "'" . $selected . ">" . $user['userName'] . "</option>";
            }
        }
        return $userChoiceList;
    }

}