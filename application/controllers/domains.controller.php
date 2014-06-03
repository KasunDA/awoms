<?php

class DomainsController extends Controller
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
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);

        // Template data
        $this->set('title', 'Domains :: View All');

        // Get domains list
        $this->set('domains', $this->getDomains());

        // Prepare Create Form
        parent::prepareForm(NULL, "ALL");
    }

    /**
     * Create
     */
    public function create()
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);

        $this->set('title', 'Domains :: Create');

        // Step 1: Create/Edit form
        if ($this->step == 1) {

            // Prepare Create Form
            parent::prepareForm(NULL, "ALL");
        }

        // Step 2: Save domain
        elseif ($this->step == 2) {

            // Data array to be passed to sql
            $data                 = array();
            $data['domainActive'] = 1;

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

        // Get updated list
        $this->set('domains', $this->getDomains());
    }

    /**
     * Edit
     */
    public function edit()
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);

        $this->set('title', 'Domains :: Edit');

        $args = func_get_args();
        if (!empty($args)) {
            $ID = $args[0];
            $this->set('domainID', $ID);
        }

        $res = TRUE;
        if ($this->step == 1) {
            $domain = $this->Domain->getDomainInfo($ID);
            $this->set('domain', $domain);

            // Get domain list
            $this->set('domains', $this->getDomains());

            // Gets input data from post, must begin with "inp_" 
            foreach ($domain as $k => $v) {
                if ($k == "brandID") {
                    $selectedBrandID = $v;
                }
                $this->set("inp_" . $k, $v);
            }

            // Prepare Create Form
            parent::prepareForm($ID, $selectedBrandID);
        } elseif ($this->step == 2) {
            // Use create method to edit existing
            $res = $this->create();
        }

        return $res;
    }

    /**
     * Get Domain List
     * 
     * Returns array of all active domains and their info
     * 
     * @return array
     */
    public function getDomains()
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);
        $domainIDs = $this->Domain->getDomainIDs('domainActive=1');
        $domains   = array();
        foreach ($domainIDs as $b) {
            $domain    = $this->Domain->getDomainInfo($b['domainID']);
            $domains[] = $domain;
        }
        return $domains;
    }

    /**
     * Generates <option> list for Domain select list use in template
     */
    public function GetDomainChoiceList($SelectedID = FALSE)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);
        $domainsList = $this->getDomains();
        if (empty($domainsList)) {
            $domainChoiceList = "<option value=''>--None--</option>";
        } else {
            $domainChoiceList = '';
            foreach ($domainsList as $domain) {
                $selected = '';
                if ($SelectedID != FALSE && $SelectedID != "ALL" && $domain['domainID'] == $SelectedID) {
                    $selected = " selected";
                }
                $domainChoiceList .= "<option value='" . $domain['domainID'] . "'" . $selected . ">" . $domain['domainName'] . "</option>";
            }
        }
        return $domainChoiceList;
    }

}