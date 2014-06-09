<form id='<?php echo $formID; ?>'  method='POST'>
  <input type='hidden' name='step' value='2' />

  <table cellpadding='2' cellspacing='0'>

    <tr>
      <td>
        Username
      </td>
      <td>
        <input type='text' id='inp_userName' name='inp_userName' value='<?php
          if (isset($inp_userName)) {
            echo $inp_userName;
          }
        ?>' size='20' />
      </td>
    </tr>
    
    <tr>
      <td>
        Passphrase
      </td>
      <td>
        <input type='password' id='inp_Passphrase' name='inp_Passphrase' value='<?php
          if (isset($inp_Passphrase)) {
            echo $inp_Passphrase;
          }
        ?>' size='20' />
      </td>
    </tr>
    
    <tr>
        <td colspan="2">
            <button class="callAPI" name="users" value="login">Log In</button>
        </td>
    </tr>
    
  </table>
</form>
<p><a href="/users/password">Lost Password?</a></p>
