<?php

class UsergroupsController extends Controller
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
        $this->set('title', 'Groups :: View All');

        // Get usergroup list
        $this->set('usergroups', $this->getUsergroups());

        // Prepare Create Form
        parent::prepareForm(NULL, "ALL");
    }

    /**
     * Create
     */
    public function create()
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);

        // Get step or assume 1st step
        empty($_REQUEST['step']) ? $step = 1 : $step = $_REQUEST['step'];
        $this->set('step', $step);
        $this->set('title', 'Groups :: Create');

        // Step 1: Create/Edit form
        if ($step == 1) {

            // Prepare Create Form
            parent::prepareForm(NULL, "ALL");
        }

        // Step 2: Save usergroup
        elseif ($step == 2) {

            // Data array to be passed to sql
            $data                    = array();
            $data['usergroupActive'] = 1;

            // Gets input data from post, must begin with "inp_"
            foreach ($_POST as $k => $v) {
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

        // Get usergroup list
        $this->set('usergroups', $this->getUsergroups());
    }

    /**
     * Edit
     */
    public function edit()
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);

        // Get step or assume 1st step
        empty($_REQUEST['step']) ? $step = 1 : $step = $_REQUEST['step'];
        $this->set('step', $step);
        $this->set('title', 'Groups :: Edit');

        $args = func_get_args();
        if (!empty($args)) {
            $ID = $args[0];
            $this->set('usergroupID', $ID);
        }

        $res = TRUE;
        if ($step == 1) {
            $usergroup = $this->Usergroup->getUsergroupInfo($ID);
            $this->set('usergroup', $usergroup);

            // Get domain list
            $this->set('usergroups', $this->getUsergroups());

            // Gets input data from post, must begin with "inp_"
            foreach ($usergroup as $k => $v) {
                if ($k == "brandID") {
                    $selectedBrandID = $v;
                }
                $this->set("inp_" . $k, $v);
            }

            // Prepare Create Form
            parent::prepareForm($ID, $selectedBrandID);
        } elseif ($step == 2) {
            // Use create method to edit existing
            $res = $this->create();
        }

        return $res;
    }

    /**
     * Get Usergroup List
     * 
     * Returns array of all active usergroups and their info
     * 
     * @return array
     */
    public function getUsergroups()
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);
        $usergroupIDs = $this->Usergroup->getUsergroupIDs('usergroupActive=1');
        $usergroups   = array();
        foreach ($usergroupIDs as $ug) {
            $usergroup    = $this->Usergroup->getUsergroupInfo($ug['usergroupID']);
            $usergroups[] = $usergroup;
        }
        return $usergroups;
    }

    /**
     * Generates <option> list for Usergroup select list use in template
     */
    public function GetUsergroupChoiceList($SelectedID = FALSE)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);
        $usergroupsList = $this->getUsergroups();
        if (empty($usergroupsList)) {
            $usergroupChoiceList = "<option value=''>--None--</option>";
        } else {
            $usergroupChoiceList = '';
            foreach ($usergroupsList as $usergroup) {
                $selected = '';
                if ($SelectedID != FALSE && $SelectedID != "ALL" && $usergroup['usergroupID'] == $SelectedID) {
                    $selected = " selected";
                }
                $usergroupChoiceList .= "<option value='" . $usergroup['usergroupID'] . "'" . $selected . ">" . $usergroup['usergroupName'] . "</option>";
            }
        }
        return $usergroupChoiceList;
    }

}