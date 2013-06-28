<?php
if (!isset($userID)) {
  $userID = 'DEFAULT';
}?>

<form id='frmCreateUser' method='POST'>
  <input type='hidden' name='step' value='2' />
  <input type='hidden' name='inp_userID' value='<?php echo $userID; ?>' />

  <table cellpadding='2' cellspacing='0'>

    <tr>
      <td>
        <!-- User -->
        User Name
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
          <!-- Active -->
          Active
      </td>
      <td>
          <select id='inp_userActive' name='inp_userActive'>
            <option value='1'<?php
              if (!isset($inp_userActive)
                || $inp_userActive == 1) {
                echo ' selected';
              }
            ?>>Active</option>
            <option value='0'<?php
              if (isset($inp_userActive)
                && $inp_userActive == 0) {
                echo ' selected';
              }
            ?>>Inactive</option>
          </select>
      </td>
    </tr>

  </table>
</form>

<?php
$pageJavaScript[] = "
/**
 * Form modal
 */

/**
 * Ajax call to save user
 */
function createUser(frmID) {
  var divResults = $('#divResults');
  var frmInput = $('#'+frmID).serialize();
  frmInput += '&m=ajax';
  console.log('CreateUser');
  var go = $.ajax({
      type: 'POST',
      url: '".DOMAINURL."users/create',
      data: frmInput
  })
  .done(function(results) {
    divResults.html(results);
    divResults.css('border', '3px solid green');
    window.setTimeout(function(){location.reload()},3000)
  })
  .fail(function(msg) {
      alert('Error: ' + msg);
  })
  .always(function() {
  });
}

/**
 * Turn form into dialog modal
 **/
$( '#frmCreateUser' ).dialog({
  autoOpen: false,
  height: 300,
  width: 650,
  modal: true,
  title: 'Create a User',
  buttons: {
    'Save User': function() {
      console.log('Saving');
      createUser($(this).attr('id'));
      $( this ).dialog( 'close' );
    },
    Cancel: function() {
      $( this ).dialog( 'close' );
    }
  }
});

/**
 * Open Modal Button Handler
 */
$( '.openModal' ).click(function() {
  console.log('modal..');
  var modalID = $(this).val();
  if (modalID == '') {
    modalID = $(this).attr('name');
  }
  $('#'+modalID).dialog('open');
});
";