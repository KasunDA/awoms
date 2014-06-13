<?php

class DomainsController extends Controller
{
    public function __construct($controller, $model, $action, $template = NULL)
    {
        parent::__construct($controller, $model, $action, $template);
    }

    /**
     * Generates <option> list for Domain select list use in template
     */
    public function GetDomainChoiceList($SelectedID = FALSE)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);
        $domainsList = $this->Domain->getAll();
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
    
    /**
     * Pre-selects brand ID
     * 
     * @param int $ID
     * @param array $data
     */
    public function prepareFormCustom($ID = NULL, $data)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);
        parent::prepareForm($ID, $data['inp_brandID']);
    }
    
    /**
     * Update
     * 
     * @param array $args
     * @return boolean
     */
    public function tupdate($args = NULL)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);

        // Load Item or Redirect to ViewAll if item doesn't exist
        $ID = parent::itemExists($args);

        if ($this->step == 1) {
            // Loads view all list
            parent::readall(FALSE);

            // Prepare Create Form
            self::prepareForm($ID);

            return true;
        } elseif ($this->step == 2) {
            // Use create method to update existing
            $res = parent::create();
            $this->set('success', $res);
            return $res;
        }
    }
}