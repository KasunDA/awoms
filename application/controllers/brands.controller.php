<?php

class BrandsController extends Controller
{

  /**
   * Get Brand List
   * 
   * @version v00.00.00
   * 
   * Returns array of all active brands and their info
   * Also sets template variable 'brands' with array results
   * 
   * @param int $brandID Optional Brand ID
   * 
   * @return array Brand info
   */
  public function getBrands($brandID = NULL) {
    Errors::debugLogger(10, __METHOD__ . '@' . __LINE__);

    // Brand info
    if ($brandID === NULL) {
      $where = '';
    } else {
      $where = ' AND brandID = '.$brandID;
    }
    $brandIDs = $this->Brand->getBrandIDs("brandActive=1".$where);
    $brands   = array();
    foreach ($brandIDs as $b) {
      $brand    = $this->Brand->getBrandInfo($b['brandID']);
      $brands[] = $brand;
    }

    // Template data
    $this->set('brands', $brands);
    return $brands;
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