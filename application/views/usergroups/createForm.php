<?php
if (!isset($usergroupID)) {
  $usergroupID = 'DEFAULT';
}?>

<form id='frmCreateUsergroup' method='POST'>
  <input type='hidden' name='step' value='2' />
  <input type='hidden' name='inp_usergroupID' value='<?php echo $usergroupID; ?>' />

  <table cellpadding='2' cellspacing='0'>

    <tr>
      <td>
        <!-- Brand -->
        Brand (STEP: <?=$step;?>)
      </td>
      <td>
        <select name='inp_usergroupBrand'>
          <?=$brandChoiceList;?>
        </select>
      </td>
    </tr>

    <tr>
      <td>
        <!-- Usergroup -->
        Usergroup Name
      </td>
      <td>
        <input type='text' id='inp_usergroupName' name='inp_usergroupName' value='<?php
          if (isset($inp_usergroupName)) {
            echo $inp_usergroupName;
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
          <select id='inp_usergroupActive' name='inp_usergroupActive'>
            <option value='1'<?php
              if (!isset($inp_usergroupActive)
                || $inp_usergroupActive == 1) {
                echo ' selected';
              }
            ?>>Active</option>
            <option value='0'<?php
              if (isset($inp_usergroupActive)
                && $inp_usergroupActive == 0) {
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
 * Ajax call to save usergroup
 */
function createUsergroup(frmID) {
  var divResults = $('#divResults');
  var frmInput = $('#'+frmID).serialize();
  frmInput += '&m=ajax';
  console.log('CreateUsergroup');
  var go = $.ajax({
      type: 'POST',
      url: '".DOMAINURL."usergroups/create',
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
$( '#frmCreateUsergroup' ).dialog({
  autoOpen: false,
  height: 300,
  width: 650,
  modal: true,
  title: 'Create a Usergroup',
  buttons: {
    'Save Usergroup': function() {
      console.log('Saving');
      createUsergroup($(this).attr('id'));
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