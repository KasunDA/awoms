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


    //*************************//
    //***** ADMIN SECTION *****//
    //*************************//

    // Admin :: Navbar logout confirmation
    $('#admin_logout_link').click(function() {
        var r = confirm('Are you sure you want to logout?');
        if (r == false) {
            return false;
        }
    });

    // Admin :: Users :: New user group help
    $('#groupHelp').hide();
    $('#groupHelpBtn').click(function() {
        $('#groupHelp').toggle();
    });

    // Admin :: Users :: New user passphrase generator
    $('#userPassphraseGenerator').hide();
    $('#userPassphraseGeneratorBtn').click(function() {
        $('#userPassphraseGenerator').toggle();
    });

    // Admin :: Users :: New user form validation
    $('#frm_admin_newUser').validate({
        rules: {
            cartID: "required",
            userGroupID: "required",
            username: "required",
            userEmail: {
                required: true,
                email: true
            }
        }
    });

    // Admin :: Order :: Masked number1
    $('#maskedNumber1').click(function() {
        $('#maskedNumber1').hide();
        $('#number1').show();
    });
    $('#number1').click(function() {
        $('#number1').hide();
        $('#maskedNumber1').show();
    });

    // Admin :: Order :: Capture Payment (Charge Customer)
    $('#capturePayment').click(function() {
        var r = confirm('Are you sure you want to charge the customer for the Total Order Price shown?');
        if (r === false) {
            return false;
        }
    });

    // Admin :: Order :: Void Order
    $('#voidOrder').click(function() {
        var r = confirm('Are you sure you want to cancel this order and reverse or void any pending charges?');
        if (r === false) {
            return false;
        }
    });

    // Admin :: Order :: Refund Order
    $('#refundOrder').click(function() {
        var r = confirm('Are you sure you want to cancel this order and reverse or refund any charges?');
        if (r === false) {
            return false;
        }
    });

    // Admin :: Customer & Orders :: Select store to generate customer select list
    $('#frm_choose_customer #cartID').change(function() {
        if ($(this).val()) {
            $('#frm_choose_customer #customerID').removeAttr('disabled');
            $('#btn_choose_all_customers_submit').removeAttr('disabled');
            var get_customerIDs = $.ajax({
                data: {m: 'ajax',
                    cartID: $(this).val()}
            })
                    .done(function(results) {
                $('#customerID_dynamic').html(results);
            })
                    .fail(function(msg) {
                alert("Error:" + msg);
            })
                    .always(function() {
            });
        }
        else {
            $('#frm_choose_customer #customerID').attr('disabled', 'disabled');
            $('#btn_choose_all_customers_submit').attr('disabled', 'disabled');
        }
    });
    $('#frm_choose_customer #cartID').trigger('change');

    // Admin :: Customer :: Validate form
    $("#frm_choose_customer").validate({
        rules: {
            cartID: "required",
            customerID: "required"
        }
    });

    // Admin :: Customer :: Edit Modal Populate Data
    $('.customerEditBtn').click(function() {
        var customerID = $(this).val();
        var lastLoadedID = $('#customerEditIDLastLoaded').val();
        // If we already loaded this CID then skip POST and just show modal
        if (customerID == lastLoadedID) {
            $('#customerEditModal').modal('show');
            return true;
        }
        var go = $.ajax({
            type: "POST",
            data: {m: 'ajax',
                a: 'getCustomerInfo',
                customerID: customerID}
        })
                .done(function(results) {
            $('#customerEditModalDynamic').html(results);
            $('#customerEditModal').modal('show');
            $('#customerEditIDLastLoaded').val(customerID);
        })
                .fail(function(msg) {
            alert("Error:" + msg);
        })
                .always(function() {
        });
    });

});
// END#document.ready