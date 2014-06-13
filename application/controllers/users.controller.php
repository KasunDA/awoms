<?php

class UsersController extends Controller
{
    public function __construct($controller, $model, $action, $template = NULL)
    {
        parent::__construct($controller, $model, $action, $template);
    }

    /**
     * Generates <option> list for User select list use in template
     */
    public function GetUserChoiceList($SelectedID = FALSE)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);
        $usersList = $this->getUsers();
        if (empty($usersList)) {
            $userChoiceList = "<option value=''>--None--</option>";
        } else {
            $userChoiceList = '';
            foreach ($usersList as $user) {
                $selected = '';
                if ($SelectedID != FALSE && $SelectedID != "ALL" && $user['userID'] == $SelectedID) {
                    $selected = " selected";
                }
                $userChoiceList .= "<option value='" . $user['userID'] . "'" . $selected . ">" . $user['userName'] . "(".$user['userEmail'].")</option>";
            }
        }
        return $userChoiceList;
    }
    
    /**
     * Pre-selects group ID
     * 
     * @param int $ID
     * @param array $data
     */
    public function prepareFormCustom($ID = NULL, $data)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);
        parent::prepareForm($ID, FALSE, FALSE, $data['inp_usergroupID']);
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
            
        } elseif ($this->step == 2) {

            // Validate login attempt...
            $username = $_POST['inp_userName'];
            $passphrase = $_POST['inp_Passphrase'];
            
            $this->set('inp_userName', $username);
            $this->set('inp_Passphrase', $passphrase);
            
            $valid = $this->User->ValidateLogin($username, $passphrase);
            
            if ($valid === FALSE)
            {
                $this->set('step', 1);
                $this->set('success', FALSE);
            } else {
                $this->set('success', TRUE);
                $_SESSION['user_logged_in'] = TRUE;
                $_SESSION['user'] = $valid;
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