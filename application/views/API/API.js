/**
 * Call API on click
 * 
 * @uses callAPI
 * 
 * Sends controller and action as parameters
 * received from this elements name/value attributes
 */
$('.callAPI').click(function() {
    console.log('.callAPI Clicked...');
    
    // Controller = name
    var controller = $(this).attr('name');
    // Action = value
    var action = $(this).val();
    
    // Confirm deletes
    if (action === "delete")
    {
        if (!confirm('Are you sure you want to PERMANENTLY DELETE this and all child objects?')) {
            return false;
        }
    }
    
    // Confirm cancel
    if (action === "cancel")
    {
        if (confirm('Are you sure you want to LOSE ALL CHANGES?')) {
            location.href = "/"+controller+"/readall";
        }
        return false;
    }
    
    // Disable button
    $(this).prop('disabled', true).addClass('button_disabled');
    $(this).html('Please wait...');
    
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
    console.log('callAPI: controller: ' + controller + ' action: ' + action + ' formID: ' + formID);

    // TinyMCE
    console.log('Checking for tinymce...');
    //if ($('.tinymce').text().length > 0)
    if ($('#inp_pageBody').length > 0)
    {
        console.log('Found tinymce...');
        tinymce.get("inp_pageBody").save();
    }
   
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
        console.log(msg);
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
    console.log('handleAPIResults controller: ' + controller + ' action: ' + action + ' formID: ' + formID + ' results: ' + results);
    var divResults = $('#divResults');
    divResults.html(results);
    divResults.show();
    // Go To Top
    document.body.scrollTop = document.documentElement.scrollTop = 0;
}

/**
 * For use with jQueryUI:
 * 
 * Open Modal Button Handler
$('.openModal').click(function() {

    var modalID = $(this).val();
    if (modalID === '') {
        modalID = $(this).attr('name');
    }

    // Open Dialog
    $('#' + modalID).removeClass('hidden');
    $('#' + modalID).dialog('open');
});
 */