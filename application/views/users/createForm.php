<form id='<?php echo $formID; ?>'  method='POST'>
  <input type='hidden' name='step' value='2' />
  <input type='hidden' name='inp_userID' value='<?php echo $userID; ?>' />

  <table cellpadding='2' cellspacing='0'>

    <tr>
      <td>
        <!-- Group -->
        Group
      </td>
      <td>
        <select name='inp_usergroupID'>
          <?=$usergroupChoiceList;?>
        </select>
      </td>
    </tr>
    
    <tr>
      <td>
        <!-- User -->
        Username
      </td>
      <td>
        <input type='text' id='inp_userName' name='inp_userName' value='<?php
          if (isset($inp_userName)) {
            echo $inp_userName;
          }
        ?>' size='60' />
      </td>
    </tr>
    
    <tr>
      <td>
        <!-- Email -->
        Email
      </td>
      <td>
        <input type='text' id='inp_userEmail' name='inp_userEmail' value='<?php
          if (isset($inp_userEmail)) {
            echo $inp_userEmail;
          }
        ?>' size='60' />
      </td>
    </tr>
    
  </table>
</form>