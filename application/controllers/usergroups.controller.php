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
        // Brands List (single db call)
        $Brand  = new Brand();
        $brands = $Brand->getWhere();

        $choiceList = "<option value='NULL'>-- None --</option>";
        $list       = $this->Usergroup->getWhere();
        if (empty($list))
        {
            return $choiceList;
        }

        $brandGroup = NULL;
        foreach ($list as $choice)
        {
            // Group by Brand
            if ($brandGroup != $choice['brandID'])
            {
                if ($brandGroup != NULL)
                {
                    $choiceList .= "</optgroup>";
                }
                $brandGroup = $choice['brandID'];
                foreach ($brands as $brand)
                {
                    if ($brand['brandID'] == $choice['brandID'])
                    {
                        break;
                    }
                }
                $choiceList .= "<optgroup label='" . $brand['brandName'] . "'>";
            }

            // Selected?
            $selected = '';
            if ($SelectedID !== FALSE && $SelectedID !== "ALL" && $choice['usergroupID'] == $SelectedID)
            {
                $selected = " selected";
            }
            $choiceList .= "
                    <option value='" . $choice['usergroupID'] . "'" . $selected . ">
                        " . $choice['usergroupName'] . "
                    </option>";
        }
        $choiceList .= "</optgroup>";

        return $choiceList;
    }

}