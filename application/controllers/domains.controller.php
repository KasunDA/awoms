<?php

class DomainsController extends Controller
{

  /**
   * Get Domain List
   * 
   * @version v00.00.00
   * 
   * Sets template data of array of all active domains and their info
   * 
   * @param int $domainID Optional Domain ID
   * @param string $domainName Option Domain Name
   * 
   * @return array Domain info
   */
  public function getDomains($domainID = NULL, $domainName = NULL) {
    Errors::debugLogger(10, __METHOD__ . '@' . __LINE__);

    // Domain info
    if ($domainID === NULL) {
      $where = '';
    } else {
      $where = ' AND domainID = '.$domainID;
    }
    // Domain name
    if ($domainName === NULL) {
      $where = '';
    } else {
      $where = " AND domainName = '".$domainName."'";
    }
    $domainIDs = $this->Domain->getDomainIDs("domainActive=1".$where);
    $domains   = array();
    foreach ($domainIDs as $d) {
      $domain    = $this->Domain->getDomainInfo($d['domainID']);
      $domains[] = $domain;
    }
    $this->set('domains', $domains);
  }
  
/**
   * Lookup Domain List
   * 
   * @version v00.00.00
   * 
   * Returns array of all active domains and their info
   * 
   * @param int $domainID Optional Domain ID
   * @param string $domainName Option Domain Name
   * 
   * @return array Domain info
   */
  public function lookupDomains($domainID = NULL, $domainName = NULL) {
    Errors::debugLogger(10, __METHOD__ . '@' . __LINE__);

    // Domain info
    if ($domainID === NULL) {
      $where = '';
    } else {
      $where = ' AND domainID = '.$domainID;
    }
    // Domain name
    if ($domainName === NULL) {
      $where = '';
    } else {
      $where = " AND domainName = '".$domainName."'";
    }
    $domainIDs = $this->Domain->getDomainIDs("domainActive=1".$where);
    $domains   = array();
    foreach ($domainIDs as $d) {
      $domain    = $this->Domain->getDomainInfo($d['domainID']);
      $domains[] = $domain;
    }
    return $domains;
  }
  

  /**
   * Create
   */
  public function create() {

    // Get step or assume 1st step
    empty($_REQUEST['step']) ? $step = 1 : $step = $_REQUEST['step'];
    $this->set('step', $step);
    $this->set('title', 'Domains :: Create');

    // Step 2: Save domain
    if ($step == 2) {

      // Data array to be passed to sql
      $data = array();

      // Gets input data from post, must begin with "inp_"
      foreach ($_POST as $k => $v) {
        if (!preg_match('/^inp_(.*)/', $k, $m)) {
          continue;
        }
        $this->set($k, $v);
        // Domain id (new or existing)
        if ($k == 'inp_domainID') {
          $inp_domainID = $v;
        }
        // Domain info col/data
        $data[$m[1]] = $v;
      }

      // Save domain info, getting ID
      $domainID = $this->Domain->update($data);
      if ($inp_domainID != 'DEFAULT') {
        // Updated (existing ID)
        $domainID = $inp_domainID;
      }
      $this->set('domainID', $domainID);
    }
  }

}