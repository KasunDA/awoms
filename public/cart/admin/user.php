<?php
// Security check: Deny direct file access; must be loaded through index getfile
if (count(get_included_files()) == 1) {
    header("Location: index.php");
    die("403");
}
?>

<!--BEGIN#cart_admin_user_container-->
<div id="cart_admin_user_container" class="container">

    <?php
    // No Action
    if (!isset($_REQUEST['a'])) {
        ?>
        <div class='page-header'>
            <h1>User Management<small>&nbsp;
                    <?php
                    $user                  = new killerCart\User();
                    $userCount['Active']   = $user->getUserCount('Active');
                    $userCount['Inactive'] = $user->getUserCount('Inactive');
                    $userCount['All']      = $userCount['Active'] + $userCount['Inactive'];
                    echo "Total (" . $userCount['All'] . "), Active (" . $userCount['Active'] . "), Inactive (" . $userCount['Inactive'] . ")";
                    ?>
                </small></h1>
        </div>
        <!--#page-header-->
        <?php
        if ($_SESSION['user']['usergroup']['usergroupID'] <= 2) {
            // New User Form
            include(cartPrivateDir . 'templates/'.$_SESSION['cartTheme'].'/admin/user/user_new_form.inc.phtml');

            // Existing User List
            include(cartPrivateDir . 'templates/'.$_SESSION['cartTheme'].'/admin/user/user_list.inc.phtml');
        }

        // Action Submitted
    } elseif (isset($_REQUEST['a'])) {

        // Action: edit_user
        if ($_REQUEST['a'] == "edit_user") {

            //
            // ACL Check
            //
            if ($_SESSION['user']['usergroup']['usergroupID'] > 2 && $_SESSION['user']['userID'] != $_REQUEST['userID']) {
                die('403');
            }

            // Initialize User Object
            $user = new killerCart\User();

            // Load selected user info
            if (!empty($_REQUEST['userID'])) {
                $userID = $_REQUEST['userID'];
                $u      = $user->getUserInfo($userID);
                echo "<h2>User " . $u['username'] . "<small>&nbsp;";
            } else {
                echo '<h2>New User <small>&nbsp;';
            }

            // Edit User (Main)
            if (empty($_REQUEST['ia'])) {

                // Step 1: Show Edit User Form & inner action menu
                if (empty($_POST['s'])) {

                    // User Edit Form
                    include(cartPrivateDir . 'templates/'.$_SESSION['cartTheme'].'/admin/user/user_edit.inc.phtml');

                    // Step 2: Save User (Add or Edit)
                } elseif ($_POST['s'] == 2) {

                    echo 'Save User</small></h2>';

                    // Sanitize (POST only!)
                    $args = array('userID'       => FILTER_SANITIZE_NUMBER_INT,
                        'cartID' => FILTER_SANITIZE_NUMBER_INT,
                        'userGroupID'  => FILTER_SANITIZE_NUMBER_INT,
                        'userActive'   => FILTER_SANITIZE_NUMBER_INT,
                        'username'     => FILTER_SANITIZE_SPECIAL_CHARS,
                        'passphrase'   => FILTER_SANITIZE_SPECIAL_CHARS,
                        'userEmail'    => FILTER_SANITIZE_EMAIL,
                        'userNotes'    => FILTER_UNSAFE_RAW
                    );
                    $s    = new killerCart\Sanitize();
                    $san  = $s->filterArray(INPUT_POST, $args);
                    if (!$san) {
                        $validated = false;
                        trigger_error('Unsanitary.', E_USER_ERROR);
                        return false;
                        // @todo
                    } else {
                        $validated = true;
                    }

                    //
                    // ACL Check
                    //
                    // [x] Checks if allowed to add users
                    // [x] Checks if allowed to add global admin
                    // [x] Checks if trying to add to other cart IDs
                    //
                    $aclCheck = FALSE;
                    $aclFailReason = 'Default...';
                    // Not a Global Admin
                    if (empty($_SESSION['user']['ACL']['global']['write'])) {
                        // Is a Cart Admin
                        if (!empty($_SESSION['user']['ACL']['cart']['write'])) {
                            // Only allow this cart
                            if ($san['cartID'] != $_SESSION['cartID']) {
                                // Allow all groups but Global Admin
                                if ($san['userGroupID'] == 1) {
                                    // Allow all users of this cart
                                    $aclCheck = FALSE;
                                    $aclFailReason = 'Not global admin, Is cart admin... BUT NOT allowed to set Global Admin group!';
                                } else {
                                    $aclCheck = TRUE;
                                }
                            } else {
                                $aclCheck = TRUE;
                            }
                        } else {
                            // Not a cart admin
                            // Only allow this cart
                            if ($san['cartID'] != $_SESSION['cartID']) {
                                $aclFailReason = 'Not global admin, Not cart admin... BUT NOT allowed to modify Cart ID!';
                                $aclCheck = FALSE;
                            }
                            // Only allow this group
                            if ($san['userGroupID'] != $_SESSION['user']['usergroup']['usergroupID']) {
                                $aclFailReason = 'Not global admin, Not cart admin... BUT NOT allowed to modify Group ID!';
                                $aclCheck = FALSE;
                            }
                            // Only allow this user
                            if ($san['userID'] != $_SESSION['user']['userID']) {
                                $aclFailReason = 'Not global admin, Not cart admin... BUT NOT allowed to modify User ID!';
                                $aclCheck = FALSE;
                            }
                            // If passes all above... allowed
                            $aclCheck = TRUE;
                        }
                    } else {
                        // Is global admin
                        $aclCheck = TRUE;
                    }
                    // ACL Denied Access
                    if ($aclCheck === FALSE) {
                        \Errors::debugLogger(__FILE__ . ': Security threat detected!');
                        trigger_error('Unexpected expected results: Security threat detected! '.$aclFailReason, E_USER_ERROR);
                        die();
                    }

                    // Active if new
                    if (!isset($san['userActive']) || ($san['userActive'] == '')) {
                        $san['userActive'] = 1;
                        $newUser           = TRUE;
                    } else {
                        $newUser = FALSE;
                    }

                    // Save User
                    $userID = $user->saveUser($_SESSION['unprotPrivKey'], $san['username'], $san['passphrase'], $san['userEmail'],
                                              $san['userActive'], $san['userNotes'], $san['userID']);
                    // Success
                    // Set User Group and Display Success Message
                    if (!empty($userID) && $user->setUserGroup($san['cartID'], $userID, $san['userGroupID'])) {
                        ?>
                        <div class="alert alert-block alert-success span6 offset3">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <h4>Success!</h4>
                            User saved successfully. Click <a href='?p=user'>here</a> to return to Users.
                        </div>
                        <?php
                        // Email notify (new user only)
                        if ($newUser === TRUE) {
                            $body = "
                                <h3>Welcome new user!</h3>
                                An account has been created for you with the details below:<br />
                                <br />
                                Admin URL: <a href='" . cartPublicUrl . "admin'>" . cartPublicUrl . "admin</a><br />
                                Username: " . $san['username'] . "<br />
                                Passphrase: " . $san['passphrase'] . "<br />
                                <br />
                                Please log in and change your passphrase immediately.";
                            if (killerCart\Email::sendEmail($san['userEmail'], $san['userEmail'], $san['userEmail'], null, null,
                                                            'New User Account', $body)) {
                                ?>
                                <div class="alert alert-block alert-success span6 offset3">
                                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                                    <h4>Email Success!</h4>
                                    User email notification was sent successfully.
                                </div>
                                <?php
                                // Email Failure
                            } else {
                                ?>
                                <div class="alert alert-block alert-error span6 offset3">
                                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                                    <h4>Email Error!</h4>
                                    Unable to email new User. Details have been logged and emailed to the administrator.
                                </div>
                                <?php
                            }
                        }

                        // Failure
                    } else {
                        ?>
                        <div class="alert alert-block alert-error span6 offset3">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <h4>Error!</h4>
                            Unable to save User. Details have been logged and emailed to the administrator. Click <a href='?p=user'>here</a> to return to Users.
                        </div>
                        <?php
                    }
                } #end:step
            }
        } #end:edit_user
    } #end:ifPost
    ?>
</div>
<!--END#cart_admin_user_container-->