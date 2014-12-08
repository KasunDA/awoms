$(document).ready(function() {

    // Admin :: Products :: Select cart to generate category and parent product select lists
    $('#frm_admin_new_product #productCartID').change(function() {
        if ($(this).val()) {
            $('#frm_admin_new_product #productCategoryID').removeAttr('disabled');
            $('#frm_admin_new_product #productParentID').removeAttr('disabled');
            var GO = $.ajax({
                data: {m: 'ajax',
                    productCartID: $(this).val(),
                    a: 'category'}
            })
            .done(function(results) {
                $('#productCategoryID_dynamic').html(results);
            })
            .fail(function(msg) {
                alert('Error:' + msg);
            })
            .always(function() {
            });
            var GO = $.ajax({
                data: {m: 'ajax',
                    productCartID: $(this).val(),
                    a: 'product'}
            })
            .done(function(results) {
                $('#productParentID_dynamic').html(results);
            })
            .fail(function(msg) {
                alert('Error:' + msg);
            })
            .always(function() {
            });
        }
        else {
            $('#frm_admin_new_product #productCategoryID').attr('disabled', 'disabled');
            $('#frm_admin_new_product #productParentID').attr('disabled', 'disabled');
        }
    });
    $('#frm_admin_new_product #productCartID').trigger('change');

    // Admin :: Product :: Privacy help button
    $('#productPrivacyHelpModalBtn').click(function() {
        $('#productPrivacyHelpModal').toggleClass('hidden');
    });

    // Admin :: Product :: Privacy help button
    $('#productTaxableHelpModalBtn').click(function() {
        $('#productTaxableHelpModal').toggleClass('hidden');
    });

    // Admin :: Product :: New product form validation
    $('#frm_admin_new_product').validate({
        rules: {
            productCartID: 'required',
            productCategoryID: 'required',
            productName: 'required',
            productPrice: {
                required: true,
                number: true
            },
            productEmail: {
                email: true
            },
            productImages: {
                required: false,
                accept: 'image/*'
            }
        },
        messages: {
            productImages: '*Please select a valid image file'
        }
    });

    // Admin :: Product :: New Product Modal :: Create Product
    $('#createProductBtn').click(function() {
        var v = $('#frm_admin_new_product').valid();
        if (v === false) {
            // alert('One or more fields on the parent form did not pass validation. Please check your fields and try again.');
            return false;
        }
        var BTN = $(this);
        var oldHtml = BTN.html();
        BTN.prop('disabled', true).html('<i class=\'icon-refresh icon-white\'></i>&nbsp;Saving...');
        var GO = $.ajax({
            type: 'POST',
            data: $('#frm_admin_new_product').serialize()
        })
        .done(function(results) {
            //$('#theRest').addClass('hidden');
            $('#results').html(results);
            BTN.html('<i class=\'icon-ok icon-white\'></i>&nbsp;Done!');
            delayedRefresh();
        })
        .fail(function(msg) {
            alert('Error: ' + msg);
        })
        .always(function() {
        });
    });

});