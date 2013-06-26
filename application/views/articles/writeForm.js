/**
 * Call API Ajax Handler
 * 
 * @version v00.00.00
 * 
 * @param {type} controller
 * @param {type} action
 * @param {type} callback
 * @returns {undefined}
 */
function callAPI(controller, action, callback) {
  // Debug
  console.debug('callAPI: ' + controller + ' action: ' + action);

  // Ajax execute
  var go = $.ajax({
     type: 'POST',
     url: 'http://api.awoms.com/' + controller + '/' + action,
     dataType: 'json',
     data: {
        m: 'ajax'
     }
 })
 .done(function(results) {
   callback(results);
 })
 .fail(function(msg) {
    // Error results
    console.debug('Sorry! We ran into an issue processing your request. The webmaster has been alerted.');
    console.debug(msg);
 })
 .always(function() {
 });
}

/**
 * populateBrandSelectList
 * 
 * @param {type} results
 * @returns {undefined} */
//window['populateBrandSelectList'] = function (results) {
function populateBrandSelectList(results) {
  // Handle Results
    $.each(results, function(index, element) {
      var brandID = element['brandID'];
      var brandName = element['brandName'];
      // Append selection
      $('#inp_brandID')
         .append($("<option></option>")
         .attr("value",brandID)
         .text(brandName)); 
    });
};

/**
 * Ajax call to save article
 * 
 * @param {int} frmID formID
 */
function writeArticle(frmID) {
  var divResults = $('#divResults');
  var frmInput = $('#'+frmID).serialize();
  frmInput += '&m=ajax';
  console.log('WriteArticle');
  var go = $.ajax({
      type: 'POST',
      url: 'articles/write',
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

  var modalID = $(this).val();
  if (modalID === '') {
    modalID = $(this).attr('name');
  }
  
  // Populate Brand Select List
  callAPI('brands', 'getBrands', populateBrandSelectList);
  
  // Open Dialog
  $('#'+modalID).removeClass('hidden');
  $('#'+modalID).dialog('open');
});
