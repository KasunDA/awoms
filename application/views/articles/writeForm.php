<?php
if (!isset($articleID)) {
  $articleID = 'DEFAULT';
}?>

<form id='frmWriteArticle' method='POST'>
  <input type='hidden' name='step' value='2' />
  <input type='hidden' name='inp_articleID' value='<?php echo $articleID; ?>' />

  <table cellpadding='2' cellspacing='0'>

    <tr>
      <td>
        <!-- Brand -->
        Brand
      </td>
      <td>
        <select disabled>
          <option>Coming Soon!</option>
        </select>
      </td>
    </tr>
    
    <tr>
      <td>
        <!-- Title -->
        Title
      </td>
      <td>
        <input type='text' id='inp_articleName' name='inp_articleName' value='<?php
          if (isset($inp_articleName)) {
            echo $inp_articleName;
          }
        ?>' size='60' />
      </td>
    </tr>

    <tr>
      <td>
          <!-- Short Desc -->
          Short Description
      </td>
      <td>
        <textarea id='inp_articleShortDescription' name='inp_articleShortDescription' cols='20' rows='3'><?php
          if (isset($inp_articleShortDescription)) {
            echo $inp_articleShortDescription;
          }
      ?></textarea>
      </td>
    </tr>

    <tr>
      <td>
        <!-- Long Desc -->
        Long Description
      </td>
      <td>
        <textarea id='inp_articleLongDescription' name='inp_articleLongDescription' cols='40' rows='4'><?php
          if (isset($inp_articleLongDescription)) {
            echo $inp_articleLongDescription;
          }
      ?></textarea>
      </td>
    </tr>

    <tr>
      <td>
        <!-- Keywords -->
        Keywords
      </td>
      <td>
        <input type='text' id='inp_articleKeywords' name='inp_articleKeywords' value='<?php
          if (isset($inp_articleKeywords)) {
            echo $inp_articleKeywords;
          }
        ?>' size='60' placeholder='Coming Soon!' disabled />
      </td>
    </tr>
    
    <tr>
      <td>
        <!-- Body -->
        Body
      </td>
      <td>
        <textarea id='inp_articleBody' name='inp_articleBody' cols='60' rows='8'><?php
          if (isset($inp_articleBody)) {
            echo $inp_articleBody; // @todo [] NL to BR
          }
        ?></textarea>
      </td>
    </tr>

  <!-- Adv Options -->
    <tr>
      <td>
          Active
      </td>
      <td>
          <select id='inp_articleActive' name='inp_articleActive'>
            <option value='1'<?php
              if (!isset($inp_articleActive)
                || $inp_articleActive == 1) {
                echo ' selected';
              }
            ?>>Active</option>
            <option value='0'<?php
              if (isset($inp_articleActive)
                && $inp_articleActive == 0) {
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
var tips = $( '.validateTips' );

function updateTips( t ) {
  tips
    .text( t )
    .addClass( 'ui-state-highlight' );
  setTimeout(function() {
    tips.removeClass( 'ui-state-highlight', 1500 );
  }, 500 );
}

function checkLength( o, n, min, max ) {
  if ( o.val().length > max || o.val().length < min ) {
    o.addClass( 'ui-state-error' );
    updateTips( 'Length of ' + n + ' must be between ' +
      min + ' and ' + max + '.' );
    return false;
  } else {
    return true;
  }
}

function checkRegexp( o, regexp, n ) {
  if ( !( regexp.test( o.val() ) ) ) {
    o.addClass( 'ui-state-error' );
    updateTips( n );
    return false;
  } else {
    return true;
  }
}

/**
 * Ajax call to save article
 */
function writeArticle(frmID) {
  var divResults = $('#divResults');
  var frmInput = $('#'+frmID).serialize();
  frmInput += '&m=ajax';
  console.log('WriteArticle');
  var go = $.ajax({
      type: 'POST',
      url: '".BRANDURL."articles/write',
      data: frmInput
  })
  .done(function(results) {
      divResults.html(results);
      divResults.css('border', '3px solid green');
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
$( '#frmWriteArticle' ).dialog({
  autoOpen: false,
  height: 600,
  width: 850,
  modal: true,
  title: 'Write an Article',
  buttons: {
    'Save Article': function() {
      console.log('Saving');
      writeArticle($(this).attr('id'));
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