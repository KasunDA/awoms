/**
 * Call API on click
 * 
 * @uses callAPI
 * 
 * Sends controller and action as parameters
 * received from this elements name/value attributes
 */
$('.callAPI').click(function() {
    console.debug('.callAPI Clicked...');
    // Controller = name
    var controller = $(this).attr('name');
    // Action = value
    var action = $(this).val();
    // Form
    var formID = $(this).parents('form:first').attr('ID');
    // Clear/Hide results div
    var divResults = $('#divResults');
    divResults.hide().html('');
    // Call API
    callAPI(controller, action, formID);
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
 * @param {int} formID
 */
function callAPI(controller, action, formID) {
    console.debug('callAPI: controller: ' + controller + ' action: ' + action + ' formID: ' + formID);

    // Serialized form data with ajax method appended
    var frmInput = $('#' + formID).serialize();
    frmInput += '&m=ajax';

    // Ajax execute
    var go = $.ajax({
        type: 'POST',
        url: '/' + controller + '/' + action,
        data: frmInput
    })
    .done(function(results) {
        // Handle Results
        handleAPIResults(controller, action, formID, results);
    })
    .fail(function(msg) {
        // Error results
        var divResults = $('#divResults');
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
function handleAPIResults(controller, action, formID, results) {
    console.debug('handleAPIResults controller: ' + controller + ' action: ' + action + ' formID: ' + formID + ' results: ' + results);
    var divResults = $('#divResults');
    divResults.html(results);
    divResults.show();
}

/**
 * Open Modal Button Handler
 */
$('.openModal').click(function() {

    var modalID = $(this).val();
    if (modalID === '') {
        modalID = $(this).attr('name');
    }

    // Open Dialog
    $('#' + modalID).removeClass('hidden');
    $('#' + modalID).dialog('open');
});