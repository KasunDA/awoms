<?php
if ($step == 1)
{
?>
<form id='<?php echo $formID; ?>'  method='POST'>
    <input type='hidden' name='step' value='2' />

    <div class="login_form">
        <div class="login_form_left_col">
            <h1>Lost Password?</h1>
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
                            ?>' size='20' tabindex='1' /></td>
                    </tr>
                    <tr>
                        <td>
                            <button class="callAPI" name="users" value="password" tabindex='2'>Lost Password</button>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="login_form_right_col">
            Enter your username in the form and click 'Lost Password'. You will receive an email within 15 minutes with further instructions.
        </div>
    </div>

</form>
<?php
}
?>