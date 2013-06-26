/**
 * Declare Results div
 */
var divResults = $('#divResults');

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
            .attr("value", brandID)
            .text(brandName));
  });
}

/**
 * Call API on click
 * 
 * @uses callAPI
 * 
 * Sends controller and action as parameters
 * received from this elements name/value attributes
 */
$('.callAPI').click(function() {
  // Controller = name
  var controller = $(this).attr('name');
  // Action = value
  var action = $(this).val();
  // Clear/Hide results div
  divResults.hide().html('');
  // Call API
  callAPI(controller, action);
});

/**
 * Call API Ajax Handler
 * 
 * Sends request to API controller/action
 * 
 * @uses handleAPIResults
 * 
 * @param {string} controller
 * @param {string} action
 */
function callAPI(controller, action) {
  console.debug('callAPI: controller: ' + controller + ' action: ' + action);

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
    // Handle Results
    handleAPIResults(controller, action, results);
  })
          .fail(function(msg) {
    // Error results
    divResults.append('Sorry! We ran into an issue processing your request. The webmaster has been alerted.');
    console.debug(msg);
    // CSS
    divResults.css('border', '3px solid red');
  })
          .always(function() {
  });
}

/**
 * Handle API Results
 * 
 * Formats results for output. Custom blocks per controller/action.
 * 
 * @param {string} controller
 * @param {string} action
 * @param {results} results
 */
function handleAPIResults(controller, action, results) {
  console.debug('handleAPIResults controller: ' + controller + ' action: ' + action);

  // Articles
  if (controller === 'articles') {

    // Get Articles
    if (action === 'getArticles') {

      // Each Article
      $.each(results['articles'], function(index, element) {
        var articleName = element['articleName'];
        console.debug('index: ' + index);
        console.debug(results['articleBodies']);
        var articleBody = results['articleBodies'][index][0]['bodyContentText'];
        var articleBodyDate = results['articleBodies'][index][0]['bodyContentDateModified'];
        divResults.append('<br />AName: ' + articleName + ' (' + articleBodyDate + ') ' +
                '<br />' + articleBody + '<hr />');
      });

    }

  }

  // Brands
  else if (controller === 'brands') {

    // Get Brands
    if (action === 'getBrands') {

      // Each Article
      $.each(results, function(index, element) {
        var brandID = element['brandID'];
        var brandName = element['brandName'];
        divResults.append('<br />BName: ' + brandName + ' #' + brandID + '<hr />');
      });

    }
  }

  // CSS
  divResults.css('border', '3px solid green');
  // Show results div
  divResults.show();
}

/**
 * Ajax call to save article
 * 
 * @param {int} frmID formID
 */
function writeArticle(frmID) {
  var divResults = $('#divResults');
  var frmInput = $('#' + frmID).serialize();
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
$('#frmWriteArticle').dialog({
  autoOpen: false,
  height: 600,
  width: 850,
  modal: true,
  title: 'Write an Article',
  buttons: {
    'Save Article': function() {
      console.log('Saving');
      writeArticle($(this).attr('id'));
      $(this).dialog('close');
    },
    Cancel: function() {
      $(this).dialog('close');
    }
  }
});

/**
 * Open Modal Button Handler
 */
$('.openModal').click(function() {

  var modalID = $(this).val();
  if (modalID === '') {
    modalID = $(this).attr('name');
  }

  // Populate Brand Select List
  callAPI('brands', 'getBrands', populateBrandSelectList);

  // Open Dialog
  $('#' + modalID).removeClass('hidden');
  $('#' + modalID).dialog('open');
});