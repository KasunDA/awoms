<?php

class ExamplesController extends Controller
{

    public function __construct($controller, $model, $action, $template = NULL)
    {
        parent::__construct($controller, $model, $action, $template);
    }

    /**
     * @var static array $staticData
     */
    public static $staticData = array();

    /**
     * @param int $ID formID
     * @param array $data
     */
//    public function prepareFormCustom($ID = NULL, $data)
//    {
//        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);
//        parent::prepareForm($ID, $data);
//    }

    /**
     * Controller specific input filtering on save, for use with createStepFinish method
     *
     * @param string $k
     * @param array|string $v
     *
     * @return boolean True confirms match (excluding from parent model save) | False nothing was done (includes in parent model save)
     */
    public static function createStepInput($k, $v)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);

        // Remove these attributes from being saved in main model
        // These are other models that will be saved in createStepFinish via the static array
        // Example:
//        if (in_array($k, array('inp_exampleName'))) {
//            self::$staticData[$k] = $v;
//            return true;
//        }
//        return false;
    }

    /**
     * Controller specific pre-save method
     *
     * e.g. inserts controller specific data such as page datetime, isActive
     *
     * @return array data to be merged into model->save($data)
     */
    public static function createStepPreSave()
    {
        // Look in specific Controller
        // EXAMPLE:
//        $data = array();
//        $data['pageActive'] = 1;
//        $data['userID'] = $_SESSION['user']['userID'];
//
//        // Post time
//        $now                          = Utility::getDateTimeUTC();
//        $data['pageDatePublished']    = $now;
//        $data['pageDateLastReviewed'] = $now;
//        $data['pageDateLastUpdated']  = $now;
//
//        return $data;
    }

    /**
     * Controller specific finish Create step after first input save createStepInput
     *
     * @param string $id
     *
     * @return boolean
     */
    public static function createStepFinish($id, $data)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);

        // Now that the parent model data is saved, you can do whatever is needed with self::$staticData and link it to the parent model id $id
        if (empty(self::$staticData))
        {
            return true;
        }

        // Example:
        // Child model example
//        $Child = new Child();
//        $data = array();
//        for ($i = 0; $i < count(self::$staticData['inp_exampleName']); $i++) {
//            $colValue = self::$staticData['inp_exampleName'][$i];
//            $data['parentID']       = $id;
//            $data['col']    = $colValue;
//            $childID = $Child->update($data);
//        }
        return true;
    }

    /**
     * Controller specific finish Delete step after first input delete but...
     * This is ran BEFORE deleting the main model ID and should be used to delete child objects for example
     */
    public static function deleteStepFinish($args = NULL)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);

        // Example:
        // Remove child objects so we can delete parent model
//        $ID       = $args;
//        $Child = new Child();
//        $Child->delete(array('parentID' => $ID));
    }

    /**
     * Generates <option> list for select list use in template
     */
    public function GetChoiceList($SelectedID = FALSE)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);

        // Example:
//        $list = $this->Example->getWhere();
//        if (empty($list)) {
//            $choiceList = "<option value=''>-- None --</option>";
//        } else {
//            $choiceList = "<option value=''>-- None --</option>";
//            foreach ($list as $choice) {
//                $selected = '';
//                if ($SelectedID !== FALSE && $SelectedID !== "ALL" && $choice['exampleID'] == $SelectedID) {
//                    $selected = " selected";
//                }
//                $choiceList .= "<option value='" . $choice['exampleID'] . "'" . $selected . ">" . $choice['exampleName'] . "</option>";
//            }
//        }
//        return $choiceList;
    }

}