<?php

/**
 * Store controller (skeleton)
 */
class StoresController extends Controller
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
    public function prepareFormCustom($ID = NULL, $data)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);

        /* Store specific form list preparation */
        if (empty($data['store']))
        {
            $data['store'] = NULL;
        }

        // Coding
        $this->set('codingChoiceList', self::GetCodingChoiceList($data['store']));

        // Contacts
        $this->set('contactsChoiceList', self::GetContactsChoiceList($data['store']));

        // Services
        $this->set('servicesChoiceList', self::GetServicesChoiceList($data['store']));

        // Hours
        $this->set('hoursChoiceList', self::GetHoursChoiceList($data['store']));

        parent::prepareForm($ID, $data);
    }

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
        Errors::debugLogger(func_get_args(), 10);

        // Remove these attributes from being saved in main model
        // These are other models that will be saved in createStepFinish via the static array
        // Save Address
        $colName = str_replace('inp_', '', $k);
        if (in_array($colName,
                     array('addressID', 'addressLine1', 'addressLine2', 'addressLine3', 'addressCity',
                'addressState', 'addressZipcode', 'addressCountry', 'addressNotes')))
        {
            self::$staticData['address'][$colName] = $v;
            return true;
        }

        // Copy to address, AND keep in user too (PK) (no return true)
        if (in_array($colName, array('brandID', // PK
                'phone')))
        { // Address
            self::$staticData['address'][$colName] = $v;
        }

        // Save store services in separate table
        $colName = str_replace('inp_', '', $k);
        if (in_array($colName, array('services')))
        {
            self::$staticData['services'] = $v;
            return true;
        }

        return false;
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

        $storeID = $id;
        $Store   = new Store();
        $store   = $data;
        
        /**
         * Create default Public and Private folders for brand's files
         */
        $p = ROOT.DS.'public'.DS.'file'.DS.'source'.DS.'Brands'.DS.$store['brandID'].DS.'Stores'.DS.$storeID.DS.'Public';
        if (!file_exists($p))
        {
            mkdir($p, 0777, true);
        }
        
        $p = ROOT.DS.'public'.DS.'file'.DS.'thumbs'.DS.'Brands'.DS.$store['brandID'].DS.'Stores'.DS.$storeID.DS.'Public';
        if (!file_exists($p))
        {
            mkdir($p, 0777, true);
        }
        
        $p = ROOT.DS.'public'.DS.'file'.DS.'source'.DS.'Brands'.DS.$store['brandID'].DS.'Stores'.DS.$storeID.DS.'Private';
        if (!file_exists($p))
        {
            mkdir($p, 0777, true);
        }
        
        $p = ROOT.DS.'public'.DS.'file'.DS.'thumbs'.DS.'Brands'.DS.$store['brandID'].DS.'Stores'.DS.$storeID.DS.'Private';
        if (!file_exists($p))
        {
            mkdir($p, 0777, true);
        }
        
//        $store   = $Store->getSingle(array('storeID' => $storeID));
        // Now that the parent model data is saved, you can do whatever is needed with self::$staticData and link it to the parent model id $id
        if (empty(self::$staticData))
        {
            // Create default cart for store
            Install::InstallCart($storeID, $store['brandID']);
            return true;
        }

        // Store services
        $Service = new Service();
        // Unchecked all services
        $Service->delete(array('storeID' => $storeID), "refStoreServices");
        if (!empty(self::$staticData['services']))
        {
            // Save checked store services
            foreach (self::$staticData['services'] as $serviceID)
            {
                $Service->update(array('serviceID' => $serviceID,
                    'storeID'   => $storeID), "refStoreServices");
            }
        }

        // Save Address
        if (!empty(self::$staticData['address']['phone'])
            || !empty(self::$staticData['address']['addressLine1'])
            || !empty(self::$staticData['address']['addressLine2'])
            || !empty(self::$staticData['address']['addressLine3'])
            || !empty(self::$staticData['address']['addressCity'])
            || !empty(self::$staticData['address']['addressZipcode'])
            || !empty(self::$staticData['address']['addressState'])
            || !empty(self::$staticData['address']['addressCountry'])
            || !empty(self::$staticData['address']['addressNotes'])
        )
        {
            $Address   = new Address();
            $addy      = array('addressID'           => self::$staticData['address']['addressID'],
                'phone'               => self::$staticData['address']['phone'],
                'line1'               => self::$staticData['address']['addressLine1'],
                'line2'               => self::$staticData['address']['addressLine2'],
                'line3'               => self::$staticData['address']['addressLine3'],
                'city'                => self::$staticData['address']['addressCity'],
                'zipPostcode'         => self::$staticData['address']['addressZipcode'],
                'stateProvinceCounty' => self::$staticData['address']['addressState'],
                'country'             => self::$staticData['address']['addressCountry'],
                'addressNotes'        => self::$staticData['address']['addressNotes']);
            $addressID = $Address->update($addy);

            // Sync address/store
            if (!empty($addressID))
            {
                $data  = array('storeID'   => $id,
                    'brandID'   => self::$staticData['address']['brandID'],
                    'addressID' => $addressID);
                $Store = new Store();
                $Store->update($data);
            }
        }

        return true;
    }

    /**
     * Controller specific finish Delete step after first input delete but...
     * This is ran BEFORE deleting the main model ID and should be used to delete child objects for example
     */
    public static function deleteStepFinish($args = NULL)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);

        // Remove child objects so we can delete parent model
        // Remove address
        $Address = new Address();
        $Address->delete(array('addressID' => $args), 'addresses');

        // Remove store services
        $Store = new Store();
        $Store->delete(array('storeID' => $args), 'storeServices');

        // Remove carts
        // @TODO
    }

    /**
     * Generates <option> list for select list use in template
     */
    public function GetStoreChoiceList($SelectedID = FALSE)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);
        // Brands List (single db call)
        $Brand  = new Brand();
        $brands = $Brand->getWhere();

        $choiceList = "<option value='NULL'>-- None --</option>";
        $list       = $this->Store->getWhere();
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
            if ($SelectedID !== FALSE && $SelectedID !== "ALL" && $choice['storeID'] == $SelectedID)
            {
                $selected = " selected";
            }
            $choiceList .= "
                <option value='" . $choice['storeID'] . "'" . $selected . ">
                    " . $choice['storeNumber'] . "
                </option>";
        }
        $choiceList .= "</optgroup>";

        return $choiceList;
    }

    /**
     * Generates <option> list for Coding select list use in template
     */
    public function GetCodingChoiceList($data)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);

        $codings = array('O'   => 'O - Open',
            'C'   => 'C - Closed',
            'N'   => 'N - New, not yet Open',
            'T'   => 'T - Transfer',
            'O/C' => 'O/C - Closed, owner has other stores still Open'); // THIS store is closed, BUT user has OTHER stores that are OPEN

        $choiceList = "<option value=''>-- None --</option>";
        foreach ($codings as $code => $codeLabel)
        {
            $selected = '';
            if ($code == $data['coding'])
            {
                $selected = " selected";
            }
            $choiceList .= "<option value='" . $code . "'" . $selected . ">" . $codeLabel . "</option>";
        }

        return $choiceList;
    }

    /**
     * Generates <option> list for Services list use in template
     */
    public function GetServicesChoiceList($data)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);

        $Service = new Service();

        // Brands Services List
        $services = $Service->getWhere(array('brandID' => $data['brandID']));

        // Stores Service IDs
        $serviceIDs = $Service->select("*", array('storeID' => $data['storeID']), NULL, "refStoreServices");

        $choiceList = "";
        foreach ($services as $service)
        {
            $selected = '';
            foreach ($serviceIDs as $serviceID)
            {
                if ($service['serviceID'] == $serviceID['serviceID'])
                {
                    $selected = " checked";
                    break;
                }
            }

            $choiceList .= "
                <li>
                    <label style='cursor: pointer; cursor: hand;'>
                        <input type='checkbox' name='inp_services[]' value='" . $service['serviceID'] . "'" . $selected . "/>&nbsp;" . $service['serviceName'] . "&nbsp;
                    </label>
                </li>";
        }

        return $choiceList;
    }

    /**
     * Generates <option> list for Hours list use in template
     */
    public function GetHoursChoiceList($data)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);

        $days       = array('hrsMon' => 'Monday',
            'hrsTue' => 'Tuesday',
            'hrsWed' => 'Wednesday',
            'hrsThu' => 'Thursday',
            'hrsFri' => 'Friday',
            'hrsSat' => 'Saturday',
            'hrsSun' => 'Sunday');
        $choiceList = "";
        foreach ($days as $code => $codeLabel)
        {

            $choiceList .= "
            <tr>
                <td>
                    " . $codeLabel . "
                </td>
                <td>
                    <input type='text' name='inp_" . $code . "' value='" . $data[$code] . "'/>
                </td>
            </tr>";
        }
        return $choiceList;
    }

    /**
     * Generates <option> list for Contacts list use in template
     */
    public function GetContactsChoiceList($data)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);

        if (empty($data))
        {
            return false;
        }

        $Usergroup                 = new Usergroup();
        $brandsStoreOwnerUsergroup = $Usergroup->getSingle(array('brandID'       => $data['brandID'],
            'usergroupName' => 'Store Owners'));

        $User              = new User();
        $usersToChooseFrom = $User->getWhere(array('usergroupID' => $brandsStoreOwnerUsergroup['usergroupID']));

        $choiceList = "<option value='NULL'>-- Select --</option>";
        foreach ($usersToChooseFrom as $userChoice)
        {
            $selected = "";
            if ($data['ownerID'] == $userChoice['userID'])
            {
                $selected = " selected";
            }
            $choiceList .= "<option value='" . $userChoice['userID'] . "'" . $selected . ">" . $userChoice['lastName'] . ", " . $userChoice['firstName'] . "  (" . $userChoice['userName'] . ")</option>";
        }
        return $choiceList;
    }

}