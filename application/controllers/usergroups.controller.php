<?php

class UsergroupsController extends Controller
{
    public function __construct($controller, $model, $action, $template = NULL)
    {
        parent::__construct($controller, $model, $action, $template);
    }

    /**
     * Generates <option> list for Usergroup select list use in template
     */
    public function GetUsergroupChoiceList($SelectedID = FALSE)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);
        $usergroupsList = $this->Usergroup->getAll();
        if (empty($usergroupsList)) {
            $usergroupChoiceList = "<option value=''>--None--</option>";
        } else {
            $usergroupChoiceList = '';
            foreach ($usergroupsList as $usergroup) {
                $selected = '';
                if ($SelectedID != FALSE && $SelectedID != "ALL" && $usergroup['usergroupID'] == $SelectedID) {
                    $selected = " selected";
                }
                $usergroupChoiceList .= "<option value='" . $usergroup['usergroupID'] . "'" . $selected . ">(" . $usergroup['brand']['brandName'] . ") " . $usergroup['usergroupName'] . "</option>";
            }
        }
        return $usergroupChoiceList;
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