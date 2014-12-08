<?php

class User extends Model
{

    protected static function getColumns()
    {
        $cols = array('userID', 'usergroupID', 'userName', 'userActive', 'userEmail', 'passphrase', 'firstName', 'middleName', 'lastName',
            'phone', 'cellPhone', 'addressID', 'notes');
        return $cols;
    }

//
//    public function getSingle($where)
//    {
//        $res = self::getWhere($where);
//        if (!empty($res)) {
//            // Load Extended Item
//            $model      = $this->model;
//            $item       = $model::LoadExtendedItem($res[0]);
//            return $item;
//        }
//        return false;
//    }
//
    public function getWhere($where = NULL, $cols = NULL, $order = NULL, $aclWhere = NULL, $in = NULL, $loadChildren = FALSE)
    {

        if ($order == NULL)
        {
            $order = "usergroupID, lastName, firstName, userName";
        }

        // ACL: Ensure user only sees items they have permission to
        // if !empty aclwhere?

        $aclWhere          = array();
        $aclWhere['where'] = NULL;
        $aclWhere['in']    = NULL;

        // Admin: All
        // Else: users.usergroup.brand = users.usergroup.brand (done via model.class.aclWhere)
        if (!empty($_SESSION['user_logged_in']) && $_SESSION['user']['usergroup']['usergroupName'] != "Administrators")
        {
            // Matching brand's usergroup's users
            $aclWhere['where'] = 'usergroupID';
            $Usergroup         = new Usergroup();
            $ins               = $Usergroup->getWhere(array('brandID' => $_SESSION['user']['usergroup']['brandID']));
            foreach ($ins as $_in)
            {
                $aclWhere['in'] .= $_in['usergroupID'] . ",";
            }
            $aclWhere['in'] = substr($aclWhere['in'], 0, -1);

            $in = NULL;
        }

        return parent::getWhere($where, $cols, $order, $aclWhere, $in, $loadChildren);
    }

    /**
     * Returns all items of model
     *
     * @param array|string $cols Columns to return if not *
     * @param string $order Order to return (col names)
     *
     * @return array
     */
    public static function userHasAccessToItem($controller, $item = NULL)
    {
        // Access Granted:
        if (
            $_SESSION['user']['usergroup']['usergroupName'] == "Administrators" || ($_SESSION['user']['usergroup']['usergroupName'] == "Store Owners" &&
            $_SESSION['user']['userID'] == $item['userID']))
        {
            return true;
        }

        // ACCESS DENIED: Return to readall page
        Errors::debugLogger(__METHOD__ . ': ACCESS DENIED...');
        header('Location: /' . $controller . '/readall');
        exit(0);
    }

    public function update($data, $table = NULL)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);

        // Passphrase
        if (!empty($data['passphrase']))
        {
            $plain              = $data['passphrase'];
            $hashedPassphrase   = self::getPassphraseHash($data['passphrase']);
            $data['passphrase'] = $hashedPassphrase;
        }
        else
        {
            if (isset($data['passphrase']))
            {
                unset($data['passphrase']);
            }
        }

        // DB Changes
        $userID = parent::update($data, $table);

        // Creating user
        if ($data['userID'] == 'DEFAULT')
        {
            $Usergroup = new Usergroup();

            // User settings
            $userSettings                   = array();
            $userSettings['userID']         = $userID;
            $userSettings['dateRegistered'] = Utility::getDateTimeUTC();
            $userSettings['registrationIP'] = Utility::getVisitorIP();
            $Usergroup->update($userSettings, "userSettings");

            // Administrator? Create brand's pub/priv keys
            // Store Owner? Create store's pub/priv keys
            $usergroup = $Usergroup->getSingle(array('usergroupID' => $data['usergroupID']));
            if ($usergroup['usergroupName'] == "Administrators" // @TODO ??
                || $usergroup['usergroupName'] == "Store Owners")
            {
                /*                 * ************************************************* */
                // @TODO ???????
                /*                 * ************************************************* */
                // Brand's Cart ID?
                $Brand = new Brand();
                $brand = $Brand->getSingle(array('brandID' => $usergroup['brandID']));

                $Auth                         = new killerCart\Auth();
                $Auth->makeCartUserKeys($brand['cartID'], $userID, $plain); //$data['passphrase']); // sets $_SESSION['unprotPrivKey']
                /*                 * ************************************************* */
                // Protected private key
                $opensslConfigPath            = OPENSSL_CONFIG;
                $protPrivKey                  = $Auth->extractProtectedPrivateKey($_SESSION['unprotPrivKey'], $plain,
                                                                                  $opensslConfigPath); //$data['passphrase']);
                $data[':protectedPrivateKey'] = base64_encode($protPrivKey);
                /*                 * ************************************************* */
            }
        }

        return $userID;
    }

    /**
     * Load additional model specific info when getWhere is called
     *
     * @param type $ID
     */
    public static function LoadExtendedItem($item)
    {

        // Group
        $Usergroup         = new Usergroup();
        $item['usergroup'] = $Usergroup->getSingle(array('usergroupID' => $item['usergroupID']));

        // Brand
        $Brand = new Brand();
//        $item['brand'] = $Brand->getSingle(array('brandID' => $item['usergroup']['brandID']));
        // Address
        if (!empty($item['addressID']))
        {
            $Address         = new Address();
            $item['address'] = $Address->getSingle(array('addressID' => $item['addressID']));
        }
        else
        {
            $item['address'] = NULL;
        }

        // Settings
        $settings = $Brand->select("*", array('userID' => $item['userID']), NULL, "userSettings");
        if (!empty($settings))
        {
            $item['settings'] = $settings[0];
        }
        else
        {
            $item['settings'] = NULL;
        }

        return $item;
    }

    /**
     * Validates user login from db, returning false or user info
     *
     * @param type $username
     * @param type $passphrase
     * @return array|boolean Array - valid login | False - invalid login
     */
    public function ValidateLogin($username, $passphrase)
    {
        Errors::debugLogger(__METHOD__ . ': username: ' . $username . ' brandid: ' . BRAND_ID);

        $UserGroup = new UserGroup();
        $groups    = $UserGroup->getWhere(array('brandID'         => BRAND_ID, 'usergroupActive' => 1));
        if (empty($groups))
        {
            return false;
        }

        // Valid login?
        $in = array();
        foreach ($groups as $group)
        {
            $in[] = $group['usergroupID'];
        }

        // Load user (with extended info (group))
        $_user = self::getSingle(array('userActive'  => 1,
                'userName'    => $username,
                'usergroupID' => NULL), // Gets IN (xxx) appended to it
                                 $in, TRUE);

        if (empty($_user))
        {
            return false;
        }

        if (crypt($passphrase, $_user['passphrase']) == $_user['passphrase'])
        {
            //$_user = self::LoadExtendedItem($_user);
            // Update user settings (success login)
            $updatedUser = array('userID'              => $_user['userID'],
                'lastLoginDate'       => Utility::getDateTimeUTC(),
                'lastLoginIP'         => Utility::getVisitorIP(),
                'failedLoginAttempts' => 0);
            $UserGroup->update($updatedUser, "userSettings");
            return $_user;
        }

        // Update user settings (failed login)
        $updatedUser = array('userID'              => $_user['userID'],
            'lastFailedLoginDate' => Utility::getDateTimeUTC(),
            'lastFailedLoginIP'   => Utility::getVisitorIP(),
            'failedLoginAttempts' => ($_user['settings']['failedLoginAttempts'] + 1));
        $UserGroup->update($updatedUser, "userSettings");

        return false;
        // $_SESSION['unprotPrivKey'] = $this->getUnprotectedPrivateKey($this->getCartUsersProtectedPrivateKey($_SESSION['userID']), $password);
    }

    /**
     * Gets hash of provided plaintext passphrase
     *
     * @var string $rounds If supported, the number of rounds to loop the hash which increases security
     * @var string $algo Crypt algorithm
     * @var string $salt Uniqid with more entropy
     * @var string $cryptSalt Salt to use in crypt
     * @param string $passphrase Plaintext passphrase to hash
     *
     * @static
     *
     * @return string Hashed passphrase
     */
    public static function getPassphraseHash($passphrase)
    {
        Errors::debugLogger(__METHOD__, 5);
        // Select highest available Crypt algorithm
        $rounds = '';
        if (CRYPT_SHA512 == 1):
            $algo   = '6';
            $rounds = '$rounds=5046';
        elseif (CRYPT_SHA256 == 1):
            $algo   = '5';
            $rounds = '$rounds=5045';
        elseif (CRYPT_BLOWFISH == 1):
            if (PHP_VERSIONID < 50307):
                $algo = '2a';
            else:
                $algo = '2y';
            endif;
        endif;
        $salt             = uniqid('', true);
        $cryptSalt        = '$' . $algo . $rounds . '$' . $salt;
        $hashedPassphrase = crypt($passphrase, $cryptSalt);
        return $hashedPassphrase;
    }

    /**
     * @param int $userID
     * @param string $passphrase
     *
     * @uses getPassphraseHash()
     *
     * @return boolean
     */
    public function changeUserPassphrase($userID, $passphrase)
    {
        Errors::debugLogger(__METHOD__, 5);
        $hashedPassphrase = self::getPassphraseHash($passphrase);
        $this->update(array('userID'     => $userID, 'passphrase' => $hashedPassphrase));
        return true;
    }

}
