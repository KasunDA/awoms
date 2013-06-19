<?php

class UsersController extends Controller
{

  /**
   * View All
   */
  public function viewall() {
    Errors::debugLogger(10, __METHOD__.'@'.__LINE__);

    $this->set('title', 'Users :: View All');
    $userIDs = $this->User->getUserIDs('userActive=1');
    $users = array();

    // Get all user info
    foreach ($userIDs as $b) {
      $user = $this->User->getUserInfo($b['userID']);
      $users[] = $user;
    }
    if (empty($users)) {
      $this->set('resultsMsg', 'No users yet!');
      $this->set('users', $users);
      return;
    }

    // Template data
    $this->set('users', $users);
  }
  
  /**
   * Create
   */
  public function create() {
    
    // Get step or assume 1st step
    empty($_REQUEST['step']) ? $step = 1 : $step = $_REQUEST['step'];
    $this->set('step', $step);
    $this->set('title', 'Users :: Create');

    // Step 2: Save user
    if ($step == 2) {

      // Data array to be passed to sql
      $data = array();
      
      // Gets input data from post, must begin with "inp_"
      foreach ($_POST as $k=>$v) {
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
  }

}