<?php

class UsergroupsController extends Controller
{

  /**
   * View All
   */
  public function viewall() {
    Errors::debugLogger(10, __METHOD__.'@'.__LINE__);

    // Template data
    $this->set('title', 'Usergroups :: View All');
    
    // Brand selection
    $brands = new BrandsController('brands', 'brand', 'getBrandList');
    
    
    $brandsList = $brands->getBrandList();
    if (empty($brandsList)) {
      $brandChoiceList = "<option value=''>--None--</option>";
    } else {
      $brandChoiceList = '';
      foreach ($brandsList as $brand) {
        $brandChoiceList .= "<option value='".$brand['brandID']."'>".$brand['brandName']."</option>";
      }
    }
    $this->set('brandChoiceList', $brandChoiceList);    
    
    // Usergroup info
    $usergroupIDs = $this->Usergroup->getUsergroupIDs('usergroupActive=1');
    $usergroups = array();
    foreach ($usergroupIDs as $b) {
      $usergroup = $this->Usergroup->getUsergroupInfo($b['usergroupID']);
      $usergroups[] = $usergroup;
    }    
    if (empty($usergroups)) {
      $this->set('resultsMsg', 'No usergroups yet!');
    }
    $this->set('usergroups', $usergroups);

  }
  
  /**
   * Create
   */
  public function create() {
    
    // Get step or assume 1st step
    empty($_REQUEST['step']) ? $step = 1 : $step = $_REQUEST['step'];
    $this->set('step', $step);
    $this->set('title', 'Usergroups :: Create');
    
    // Step 2: Save usergroup
    if ($step == 2) {

      // Data array to be passed to sql
      $data = array();
      
      // Gets input data from post, must begin with "inp_"
      foreach ($_POST as $k=>$v) {
        if (!preg_match('/^inp_(.*)/', $k, $m)) {
          continue;
        }
        $this->set($k, $v);
        // Usergroup id (new or existing)
        if ($k == 'inp_usergroupID') {
          $inp_usergroupID = $v;
        }
        // Usergroup info col/data
        $data[$m[1]] = $v;
      }

      // Save usergroup info, getting ID
      $usergroupID = $this->Usergroup->update($data);
      if ($inp_usergroupID != 'DEFAULT') {
        // Updated (existing ID)
        $usergroupID = $inp_usergroupID;
      }
      $this->set('usergroupID', $usergroupID);
      
    }
  }

}