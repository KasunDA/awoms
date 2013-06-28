<?php
if (!isset($brandID)) {
  $brandID = 'DEFAULT';
}?>

<form id='frmCreateBrand' method='POST'>
  <input type='hidden' name='step' value='2' />
  <input type='hidden' name='inp_brandID' value='<?php echo $brandID; ?>' />

  <table cellpadding='2' cellspacing='0'>

    <tr>
      <td>
        <!-- Name -->
        Name
      </td>
      <td>
        <input type='text' id='inp_brandName' name='inp_brandName' value='<?php
          if (isset($inp_brandName)) {
            echo $inp_brandName;
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
        <input type='text' id='inp_brandDescription' name='inp_brandDescription' value='<?php
          if (isset($inp_brandDescription)) {
            echo $inp_brandDescription;
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
          <select id='inp_brandActive' name='inp_brandActive'>
            <option value='1'<?php
              if (!isset($inp_brandActive)
                || $inp_brandActive == 1) {
                echo ' selected';
              }
            ?>>Active</option>
            <option value='0'<?php
              if (isset($inp_brandActive)
                && $inp_brandActive == 0) {
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
 * Ajax call to save brand
 */
function createBrand(frmID) {
  var divResults = $('#divResults');
  var frmInput = $('#'+frmID).serialize();
  frmInput += '&m=ajax';
  console.log('CreateBrand');
  var go = $.ajax({
      type: 'POST',
      url: '".BRANDURL."brands/create',
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
$( '#frmCreateBrand' ).dialog({
  autoOpen: false,
  height: 300,
  width: 650,
  modal: true,
  title: 'Create a Brand',
  buttons: {
    'Save Brand': function() {
      console.log('Saving');
      createBrand($(this).attr('id'));
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