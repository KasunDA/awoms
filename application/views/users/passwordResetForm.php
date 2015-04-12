<form id='<?php echo $formID; ?>'  method='POST'>
    <input type='hidden' name='step' value='4' />
    <input type='hidden' name='userID' value='<?php echo $userID; ?>' />

    <div class="login_form">
        <div class="login_form_left_col">
            <h1>Reset Password</h1>
            <div class="login_form_box">
                <table>
                    <tr>
                        <td>New Password</td>
                    </tr>
                    <tr>
                        <td><input type='password' id='inp_userPassword' name='inp_userPassword' value='<?php
                            if (isset($inp_userPassword)) {
                                echo $inp_userPassword;
                            }
                            ?>' size='20' tabindex='1' /></td>
                    </tr>
                    <tr>
                        <td>Confirm Password</td>
                    </tr>
                    <tr>
                        <td><input type='password' id='inp_userPasswordConfirm' name='inp_userPasswordConfirm' size='20' tabindex='2' /></td>
                    </tr>
                    <tr>
                        <td>
                            <button class="callAPI" name="users" value="password" tabindex='3'>Reset Password</button>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="login_form_right_col">
            Enter your new password in the form and click 'Reset Password'.
        </div>
    </div>

</form>
