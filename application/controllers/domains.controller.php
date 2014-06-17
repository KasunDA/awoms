<?php

class DomainsController extends Controller
{
    public function __construct($controller, $model, $action, $template = NULL)
    {
        parent::__construct($controller, $model, $action, $template);
    }
    
    /**
     * Controller specific finish Create step after first input save
     * 
     * @param string $id
     * 
     * @return boolean
     */
    public static function createStepFinish($id)
    {
        $Domain = new Domain();
        $RewriteMapping = new RewriteMapping();

        // Duplicate existing rewrite rules (across all selected brands domains)
        $domain = $Domain->getSingle(array('domainID' => $id));
        $domains = $Domain->getWhere(array('brandID' => $domain['brandID']));
        foreach ($domains as $domain)
        {
            // Skipping the newly created domain of course...
            if ($domain['domainID'] == $id) continue;
            $mappings = $RewriteMapping->getWhere(array('domainID' => $domain['domainID']));
            foreach ($mappings as $mapping)
            {
                $RewriteMapping->update(array('aliasURL' => $mapping['aliasURL'],
                    'actualURL' => $mapping['actualURL'],
                    'sortOrder' => $mapping['sortOrder'],
                    'domainID' => $id));
            }
        }
    }

    /**
     * Controller specific finish Delete step after first input delete
     */
    public static function deleteStepFinish($args = NULL)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);
        $ID       = $args;
        
        // Remove existing rewrite rules (for selected domain)
        $Domain = new Domain();
        $domain = $Domain->getSingle(array('domainID' => $ID));
        $RewriteMapping = new RewriteMapping();
        $domainRules = $RewriteMapping->getWhere(array('domainID' => $ID));
        foreach ($domainRules as $domainRule)
        {
            $RewriteMapping->removeRewriteRule($domainRule['aliasURL'], $domain['domainName'], $ID);
        }
    }

    /**
     * Generates <option> list for Domain select list use in template
     */
    public function GetDomainChoiceList($SelectedID = FALSE)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);
        $domainsList = $this->Domain->getAll();
        if (empty($domainsList)) {
            $domainChoiceList = "<option value=''>-- None --</option>";
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
}