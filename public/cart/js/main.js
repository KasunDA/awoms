//****************************//
//***** GLOBAL FUNCTIONS *****//
//****************************//
function delayedRefresh(timeout) {
    if (typeof timeout === 'undefined') {
        var timeout = 500;
    }
    console.debug('delayedRefresh in: ' + timeout);
    window.setTimeout(function() {location.reload();}, timeout);
}


$(document).ready(function() {
    //***************************//
    //***** GLOBAL ON READY *****//
    //***************************//

    // Global :: Enable Tooltips
    $('.tooltip-on').tooltip();

    // Global :: Form Validation :: Defaults
    $.validator.setDefaults({
        debug: true,
        errorClass: "validationFail",
        validClass: "validationSuccess",
        onkeyup: function(element) {
            $(element).valid();
        },
        onfocusout: function(element) {
            $(element).valid();
        },
        submitHandler: function(form) {
            form.submit();
        }
    });

    // Global :: Form Validation :: Class Rules
    $.validator.addClassRules({
        validateRequired: {
            required: true
        },
        validateNumber: {
            number: true
        }
    });

    //**************************//
    //***** NAVBAR SECTION *****//
    //**************************//

    // Navbar :: Search typeahead
    var hints = ['Jeans', 'Shirts', 'Girls', 'Boys', 'Shoes'];
    $('#navbar_search_field').typeahead({source: hints});

    //****************************//
    //***** CUSTOMER SECTION *****//
    //****************************//

    // Customer :: Login form validation
    $('#frm_customer_login').validate({
        rules: {
            username: "required",
            passphrase: "required"
        }
    });

    // Customer :: Login form :: Show remember_me warning
    $('#frm_customer_login #remember_me_warning').hide();
    $('#frm_customer_login #chk_remember_me').click(function() {
        if (this.checked) {
            $('#frm_customer_login #remember_me_warning').show();
        } else {
            $('#frm_customer_login #remember_me_warning').hide();
        }
    });
    
});
// END#document.ready