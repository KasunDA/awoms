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

}