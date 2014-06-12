<?php

class MenusController extends Controller
{
    public function __construct($controller, $model, $action, $template = NULL)
    {
        parent::__construct($controller, $model, $action, $template);
    }

    public static $staticData = array();

    /**
     * Controller specific input filtering on save, for use with StepFinish method
     * 
     * @param string $k
     * @param array|string $v
     * 
     * @return boolean True confirms match | False nothing was done
     */
    public static function createStepInput($k, $v)
    {
        // Menu links are in separate tables
        if (in_array($k, array('inp_menuLinkDisplay', 'inp_menuLinkURL'))) {
            self::$staticData[$k] = $v;
            return true;
        }
        return false;
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
        /* Save menu Links */
        $MenuLink = new MenuLink();

        // First remove all links then save new list:
        $MenuLink->removeAllMenuLinks($id);

        $data = array();

        for ($i = 0; $i < count(self::$staticData['inp_menuLinkDisplay']); $i++) {
            $display = self::$staticData['inp_menuLinkDisplay'][$i];
            $url     = self::$staticData['inp_menuLinkURL'][$i];

            // Skip empty/cloneable tr
            if (empty($display) && empty($url)) {
                continue;
            }

            $data['menuID']       = $id;
            $data['sortOrder']    = $i;
            $data['parentLinkID'] = NULL;
            $data['display']      = $display;
            $data['url']          = $url;
            $data['linkActive']   = 1;

            $linkID = $MenuLink->update($data);
        }

        return true;
    }

    /**
     * Controller specific finish Delete step after first input delete
     */
    public static function deleteStepFinish($args = NULL)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);
        $ID       = $args;
        $MenuLink = new MenuLink();
        $MenuLink->delete(array('menuID' => $ID));
    }

}