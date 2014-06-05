
<form id='<?php echo $formID; ?>'  method='POST'>
    <input type='hidden' name='step' value='2' />

    <div class="login_form">
        <div class="login_form_left_col">
            <h1>Login</h1>

            <div class="login_form_box">
                <table>
                    <tr>
                        <td>Username</td>
                    </tr>
                    <tr>
                        <td><input type='text' id='inp_userName' name='inp_userName' value='<?php
                            if (isset($inp_userName)) {
                                echo $inp_userName;
                            }
                            ?>' size='20' /></td>
                    </tr>
                    <tr><td>&nbsp;</td></tr>
                    <tr>
                        <td>Password</td>
                    </tr>
                    <tr>
                        <td><input type='password' id='inp_Passphrase' name='inp_Passphrase' value='<?php
                            if (isset($inp_Passphrase)) {
                                echo $inp_Passphrase;
                            }
                            ?>' size='20' /></td>
                    </tr>
                    <tr>
                        <td>
                            <button class="callAPI" name="users" value="login">Log In</button>
                        </td>
                    </tr>
                </table>
            </div>
            <p><a href="/users/password">Forgot password?</a></p>
        </div>
        <div class="login_form_right_col">
            <img src="/css/Hut8/images/people/dreamstime_5539305.jpg"/>
        </div>
    </div>

</form>
