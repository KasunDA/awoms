<?php
if (!isset($domainID)) {
  $domainID = 'DEFAULT';
}?>

<form id='frmCreateDomain' method='POST'>
  <input type='hidden' name='step' value='2' />
  <input type='hidden' name='inp_domainID' value='<?php echo $domainID; ?>' />

  <table cellpadding='2' cellspacing='0'>

    <tr>
      <td>
        <!-- Name -->
        Name
      </td>
      <td>
        <input type='text' id='inp_domainName' name='inp_domainName' value='<?php
          if (isset($inp_domainName)) {
            echo $inp_domainName;
          }
        ?>' size='60' />
      </td>
    </tr>
    
    <tr>
      <td>
        <!-- Description -->
        Description
      </td>
      <td>
        <input type='text' id='inp_domainDescription' name='inp_domainDescription' value='<?php
          if (isset($inp_domainDescription)) {
            echo $inp_domainDescription;
          }
        ?>' size='60' />
      </td>
    </tr>

    <tr>
      <td>
          <!-- Brand -->
          Brand
      </td>
      <td>
          <select id='inp_brandID' name='inp_brandID'>
            <option value='1'<?php
              if (!isset($inp_brandID)
                || $inp_brandID == 1) {
                echo ' selected';
              }
            ?>>Activ222222e</option>
            <option value='0'<?php
              if (isset($inp_brandID)
                && $inp_brandID == 0) {
                echo ' selected';
              }
            ?>>Inactiv222222222e</option>
          </select>
      </td>
    </tr>
    
    <tr>
      <td>
          <!-- Active -->
          Active
      </td>
      <td>
          <select id='inp_domainActive' name='inp_domainActive'>
            <option value='1'<?php
              if (!isset($inp_domainActive)
                || $inp_domainActive == 1) {
                echo ' selected';
              }
            ?>>Active</option>
            <option value='0'<?php
              if (isset($inp_domainActive)
                && $inp_domainActive == 0) {
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
 * Ajax call to save domain
 */
function createDomain(frmID) {
  var divResults = $('#divResults');
  var frmInput = $('#'+frmID).serialize();
  frmInput += '&m=ajax';
  console.log('CreateDomain');
  var go = $.ajax({
      type: 'POST',
      url: '".DOMAINURL."domains/create',
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
$( '#frmCreateDomain' ).dialog({
  autoOpen: false,
  height: 300,
  width: 650,
  modal: true,
  title: 'Create a Domain',
  buttons: {
    'Save Domain': function() {
      console.log('Saving');
      createDomain($(this).attr('id'));
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