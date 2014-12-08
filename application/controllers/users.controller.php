<?php

class UsersController extends Controller
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
     * Update
     *
     * @param array $args
     * @return boolean
     */
    public function update($args = NULL)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);

        if ($this->step == 1)
        {
            // No ID passed, redirect to /update/<logged in users ID>
            if ($args == NULL)
            {
                header("Location: /users/update/" . $_SESSION['user']['userID']);
                exit(0);
            }
        }

        parent::update($args);
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

        // Remove these attributes from being saved in main model
        // These are other models that will be saved in createStepFinish via the static array
        // Save Address
        $colName = str_replace('inp_', '', $k);
        if (in_array($colName,
                     array('addressID', 'addressLine1', 'addressLine2', 'addressLine3', 'addressCity',
                'addressState', 'addressZipcode', 'addressCountry', 'addressNotes')))
        {
            self::$staticData[$colName] = $v;
            return true;
        }

        // Copy to address, keep in user too (PK)
        if (in_array($colName,
                     array('usergroupID', 'userName', 'userActive', 'userEmail', // PK
                'firstName', 'middleName', 'lastName', 'phone')))
        { // Address
            self::$staticData[$colName] = $v;
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

        // Now that the parent model data is saved, you can do whatever is needed with self::$staticData and link it to the parent model id $id
        if (empty(self::$staticData))
        {
            return true;
        }

        // Save Address
        $Address   = new Address();
        $addy      = array('addressID'           => self::$staticData['addressID'],
            'firstName'           => self::$staticData['firstName'],
            'middleName'          => self::$staticData['middleName'],
            'lastName'            => self::$staticData['lastName'],
            'phone'               => self::$staticData['phone'],
            'email'               => self::$staticData['userEmail'],
            'line1'               => self::$staticData['addressLine1'],
            'line2'               => self::$staticData['addressLine2'],
            'line3'               => self::$staticData['addressLine3'],
            'city'                => self::$staticData['addressCity'],
            'zipPostcode'         => self::$staticData['addressZipcode'],
            'stateProvinceCounty' => self::$staticData['addressState'],
            'country'             => self::$staticData['addressCountry'],
            'addressNotes'        => self::$staticData['addressNotes']);
        $addressID = $Address->update($addy);
        if (empty($addressID))
        {
            $addressID = self::$staticData['addressID'];
        }

        // Sync address/user
        $User = new User();
        $User->update(array('userID'      => $id,
            'usergroupID' => self::$staticData['usergroupID'],
            'userName'    => self::$staticData['userName'],
            'userActive'  => self::$staticData['userActive'],
            'userEmail'   => self::$staticData['userEmail'],
            'addressID'   => $addressID));

        return true;
    }

    /**
     * Controller specific finish Delete step after first input delete but...
     * This is ran BEFORE deleting the main model ID and should be used to delete child objects for example
     */
    public static function deleteStepFinish($args = NULL)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);

        $ID = $args;

        // Remove Address
        $User = new User();
        $u    = $User->getSingle(array('userID' => $ID));
        if (!empty($u['addressID']))
        {
            $Address = new Address();
            $Address->delete(array('addressID' => $u['addressID']));
        }

        return true;
    }

    /**
     * Generates <option> list for User select list use in template
     */
    public function GetUserChoiceList($SelectedID = FALSE)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);

        // Brands List (single db call)
        $Brand  = new Brand();
        $brands = $Brand->getWhere();

        // Usergroups List (single db call)
        $Usergroup  = new Usergroup();
        $usergroups = $Usergroup->getWhere();

        $choiceList = "<option value='NULL'>-- None --</option>";
        $list       = $this->User->getWhere();
        if (empty($list))
        {
            return $choiceList;
        }

        $brandGroup     = NULL;
        $usergroupGroup = NULL;
        foreach ($list as $choice)
        {
            // Group by Brand (via Usergroup) and Usergroup within Brand
            // New Group
            if ($usergroupGroup != $choice['usergroupID'])
            {

                // New or Same Brand?
                //
                // User -> Usergroup -> Brand
                foreach ($usergroups as $usergroup)
                {
                    if ($usergroup['usergroupID'] == $choice['usergroupID'])
                    {
                        foreach ($brands as $brand)
                        {
                            if ($brand['brandID'] == $usergroup['brandID'])
                            {
                                break;
                            }
                        }
                        if ($brand['brandID'] == $usergroup['brandID'])
                        {
                            break;
                        }
                    }
                }

                // End prev Group?
                if (!empty($usergroupGroup))
                {
                    $choiceList .= "\n<!-- End prev Group --></optgroup>";
                }

                // New Brand
                if ($brandGroup != $brand['brandID'])
                {
                    // End prev Brand?
                    if (!empty($brandGroup))
                    {
                        $choiceList .= "\n<!-- End prev Brand $brandGroup --></optgroup>";
                    }

                    // New Brand
                    $choiceList .= "\n<!-- New Brand --><optgroup label='" . $brand['brandName'] . "'>";
                }

                // Save group for next round
                $usergroupGroup = $choice['usergroupID'];
                // Save brand for next round
                $brandGroup     = $brand['brandID'];

                $choiceList .= "\n<!-- New Group $usergroupGroup --><optgroup label='&nbsp;&nbsp;" . $usergroup['usergroupName'] . "'>";
            }

            // Selected?
            $selected = '';
            if ($SelectedID !== FALSE && $SelectedID !== "ALL" && $choice['userID'] == $SelectedID)
            {
                $selected = " selected";
            }
            $choiceList .= "\n
                <!-- UsergroupGroup: $usergroupGroup -->
                <option value='" . $choice['userID'] . "'" . $selected . ">
                    \n" . $choice['userName'] . "
                \n</option>\n";
        }

        if (!empty($usergroupGroup))
        {
            $choiceList .= "\n<!-- End Usergroup --></optgroup>";
        }
        if (!empty($brandGroup))
        {
            $choiceList .= "\n<!-- End Brand --></optgroup>";
        }

        return $choiceList;
    }

    /**
     * Login
     */
    public function login()
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);

        $this->set('title', 'Users :: Login');
        $this->set('step', $this->step);
        $this->set('formID', "frmLoginUsers");
        $this->set('success', TRUE);

        if (!empty($_GET['logoutSuccess']))
        {
// Show logout success message if directed here from logout page
            $this->set('logoutSuccess', 1);
        }

        if (!empty($_GET['access']))
        {
// Show access denied message if directed here from ACL/403
            $this->set('access', 1);

            $returnURL = "/";
            if (!empty($_GET['returnURL']))
            {
                $returnURL = $_GET['returnURL'];
            }

            $this->set('returnURL', $returnURL);
        }

        if ($this->step == 1
            && isset($_SESSION['user_logged_in'])
            && $_SESSION['user_logged_in'] === TRUE
            && empty($_GET['access']))
        {

// User is already logged in... ( and not dealing with ACL denied redirect )
            header('Location: /owners');
            exit(0);
        }
        elseif ($this->step == 2)
        {

// Validate login attempt...
            $username   = $_POST['inp_userName'];
            $passphrase = $_POST['inp_Passphrase'];

            $this->set('inp_userName', $username);
            $this->set('inp_Passphrase', $passphrase);

            $valid = $this->User->ValidateLogin($username, $passphrase);

            if ($valid === FALSE)
            {
                $this->set('step', 1);
                $this->set('success', FALSE);
            }
            else
            {
                $this->set('success', TRUE);
                $_SESSION['user_logged_in'] = TRUE;
                $_SESSION['user']           = $valid;
                Session::saveSessionToDB();

                $returnURL = "/owners";
                if (!empty($_GET['returnURL']))
                {
                    $returnURL = $_GET['returnURL'];
                }

                $this->set('returnURL', $returnURL);
            }
        }
    }

    /**
     * Logout
     */
    public function logout()
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);

        $this->set('title', 'Users :: Logout');

        unset($_SESSION['user']);
        $_SESSION['user_logged_in'] = FALSE;

        Session::saveSessionToDB();

        Session::removeCookies();

        header('Location: /users/login?logoutSuccess=1');
        exit(0);
    }

}
