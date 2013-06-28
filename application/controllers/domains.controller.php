<?php

class DomainsController extends Controller
{

  /**
   * Get Domain List
   * 
   * @version v00.00.00
   * 
   * Returns array of all active domains and their info
   * Also sets template variable 'domains' with array results
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
      $where = ' AND domainName = '.$domainName;
    }
    $domainIDs = $this->Domain->getDomainIDs("domainActive=1".$where);
    $domains   = array();
    foreach ($domainIDs as $d) {
      $domain    = $this->Domain->getDomainInfo($d['domainID']);
      $domains[] = $domain;
    }

    // Template data
    $this->set('domains', $domains);

    // Return info
    return $domains;
  }

  /**
   * View All
   */
  public function viewall() {
    Errors::debugLogger(10, __METHOD__ . '@' . __LINE__);

    // Get domain list
    $domains = $this->getDomains();

    // Template data
    if (empty($domains)) {
      $this->set('resultsMsg', 'No domains yet!');
    }
    $this->set('title', 'Domains :: View All');
    $this->set('domains', $domains);
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