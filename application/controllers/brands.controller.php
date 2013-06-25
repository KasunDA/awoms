<?php

class BrandsController extends Controller
{

  /**
   * Get Brand List
   * 
   * Returns array of all active brands and their info
   * 
   * @return array
   */
  public function getBrands() {
    Errors::debugLogger(10, __METHOD__ . '@' . __LINE__);

    // Brand info
    $brandIDs = $this->Brand->getBrandIDs('brandActive=1');
    $brands   = array();
    foreach ($brandIDs as $b) {
      $brand    = $this->Brand->getBrandInfo($b['brandID']);
      $brands[] = $brand;
    }

    // Template data
    $this->set('brands', $brands);

    // Return info
    return $brands;
  }

  /**
   * View All
   */
  public function viewall() {
    Errors::debugLogger(10, __METHOD__ . '@' . __LINE__);

    // Get brand list
    $brands = $this->getBrands();

    // Template data
    if (empty($brands)) {
      $this->set('resultsMsg', 'No brands yet!');
    }
    $this->set('title', 'Brands :: View All');
    $this->set('brands', $brands);
  }

  /**
   * Create
   */
  public function create() {

    // Get step or assume 1st step
    empty($_REQUEST['step']) ? $step = 1 : $step = $_REQUEST['step'];
    $this->set('step', $step);
    $this->set('title', 'Brands :: Create');

    // Step 2: Save brand
    if ($step == 2) {

      // Data array to be passed to sql
      $data = array();

      // Gets input data from post, must begin with "inp_"
      foreach ($_POST as $k => $v) {
        if (!preg_match('/^inp_(.*)/', $k, $m)) {
          continue;
        }
        $this->set($k, $v);
        // Brand id (new or existing)
        if ($k == 'inp_brandID') {
          $inp_brandID = $v;
        }
        // Brand info col/data
        $data[$m[1]] = $v;
      }

      // Save brand info, getting ID
      $brandID = $this->Brand->update($data);
      if ($inp_brandID != 'DEFAULT') {
        // Updated (existing ID)
        $brandID = $inp_brandID;
      }
      $this->set('brandID', $brandID);
    }
  }

}