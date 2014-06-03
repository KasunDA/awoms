Please <a href="/users/login">click here</a> to login. If you have forgotten your password <a href="">click here</a> to reset your password.

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
    
  </table>
</form>


<?php
$pageJavaScript[] = <<<___EOF

var createFrmID = "$formID";
var createTitle = "User Login";
var createController = "Users";
var createAction = "Login";
var createSaveText = "Login";

/**
 * Apply 'Dialog' to form (jUI modal)
 **/
$('#' + createFrmID).dialog({
  autoOpen: true,
  height: 250,
  width: 400,
  modal: true,
  title: createTitle,
  buttons: {
    "Login": function() {

      console.log('Calling API...');
      callAPI(createController, createAction, createFrmID);
      $(this).dialog('close');
    },
    Cancel: function() {
      $(this).dialog('close');
    }
  }
});

___EOF;

//tset