$(document).ready(function() {
    
    function hideAllCreateOptionDivs() {
        // Hide all
        $('#divProductOptionCreateStep2').addClass('hidden');
        $('#divProductOptionCreateStep2Clone').addClass('hidden');
        $('#divProductOptionCreateStep2New').addClass('hidden');

        $('#divProductOptionCreateStep3').addClass('hidden');
        $('#divProductOptionCreateStep3Clone').addClass('hidden');
        $('#divProductOptionCreateStep3New').addClass('hidden');
        
        $('#divProductOptionSortOrder').addClass('hidden');
    }

    // Step 1: Option: Clone (click)
    $('#btnOptionClone').click(function() {
        hideAllCreateOptionDivs();
        // Show this
        $('#divProductOptionCreateStep2').removeClass('hidden');
        $('#divProductOptionCreateStep2Clone').removeClass('hidden');
        $('#divProductOptionCreateStep3').removeClass('hidden');
        $('#divProductOptionCreateStep3Clone').removeClass('hidden');
        $('#createProductOptionBtn').removeAttr('disabled');
        console.debug('List length: ' + $('#selProductOptionCreateStep2Clone').length);

        // If have not loaded existing option list yet, load/populate:
        if ($('#selProductOptionCreateStep2Clone').length === 1) {
            /**
             * populateOptionSelectList
             * 
             * @param {type} results
             * @returns {undefined}
             */
            function populateOptionSelectList(results) {
                // Clear list
                $('#selProductOptionCreateStep2Clone').find('option').remove();
                
                // Handle Results
                if (results.length !== 0) {
                    $.each(results, function(index, element) {
                        var optionID = element['optionID'];
                        var optionName = element['optionName'];
                        // Append selection
                        $('#selProductOptionCreateStep2Clone')
                                .append($("<option></option>")
                                .attr("value", optionID)
                                .text('(#' + optionID + ') ' + optionName));
                    });
                } else {
                    console.debug('No cart options found!');
                    $('#selProductOptionCreateStep2Clone')
                            .append($("<option></option>")
                            .attr("value", '')
                            .text('No options exist!'));
                }
            }

            // Ajax
            var GO = $.ajax({
                dataType: 'json',
                data: {m: 'ajax',
                    productID: $('#productID').val(),
                    a: 'getOptionsList'}
            })
                    .done(function(results) {
                console.debug('ajax');
                populateOptionSelectList(results);
                $('#selProductOptionCreateStep2Clone').trigger('change');
            })
                    .fail(function(msg) {
                alert('Error:' + msg);
            })
                    .always(function() {
            });
        }
    });
    
    // Step 2: Option: Clone: Select Option to Clone to Populate Form
    $('#selProductOptionCreateStep2Clone').change(function() {
        $('#createProductOptionBtn').removeAttr('disabled');
        var productID = $('#productID').val();
        var optionIDtoClone = $(this).val();
        var divResults = $('#divProductOptionCreateStep3Clone');
        console.log('Clone option ID changed: ' + optionIDtoClone);
        if (optionIDtoClone === '') {
            console.log('Stopping');
            return false;
        }
        var GO = $.ajax({
            data: {m: 'ajax',
                a: 'cloneOptionID',
                productID: productID,
                optionIDtoClone: optionIDtoClone
                }
        })
        .done(function(results) {
            divResults.html(results);
            $('#divProductOptionSortOrder').removeClass('hidden');
        })
        .fail(function(msg) {
            console.debug(msg);
        })
        .always(function() {
        });
    });

    // Step 1: Option: New (click)
    $('#btnOptionNew').click(function() {
        hideAllCreateOptionDivs();
        // Show this
        $('#divProductOptionCreateStep2').removeClass('hidden');
        $('#divProductOptionCreateStep2New').removeClass('hidden');
        $('#divProductOptionCreateStep3').removeClass('hidden');
        $('#divProductOptionCreateStep3New').removeClass('hidden');
    });

    // Step 2: Option: New: Select New Option Type to Populate Form
    $('#optionTypeNew').change(function() {
        $('#createProductOptionBtn').removeAttr('disabled');
        var productID = $('#productID').val();
        var optionTypeID = $(this).val();
        var divResults = $('#divProductOptionCreateStep3New');
        var GO = $.ajax({
            data: {m: 'ajax',
                a: 'type',
                productID: productID,
                optionType: optionTypeID
                }
        })
        .done(function(results) {
            divResults.html(results);
            $('#divProductOptionSortOrder').removeClass('hidden');
        })
        .fail(function(msg) {
            alert('Error:' + msg);
        })
        .always(function() {
        });
    });
    $('#optionType').trigger('change');

    // Admin :: Product :: Edit Product :: Options :: Form Validator
    $('#admin_product_edit_form').validate({
        rules: {
            optionName: 'required',
            'imageIDsNew[]': {
                required: true,
                minlength: 1
            },
            'optionParentTriggers[]': {
                required: true,
                minlength: 1
            }
        },
        messages: {
            productImages: '*Please select a valid image file'
        }
    });

    // Admin :: Product :: Edit Product Option Modal :: Create Option (click)
    $('#createProductOptionBtn').click(function() {
        var cloneVal = $('#optionIsClone').val();
        console.debug('optionIsClone:' + cloneVal);
        console.debug(cloneVal);
        if (parseInt(cloneVal) >= 1) {
            // Is Clone not new, remove new fields
            console.debug('Is Clone Not new, removing new fields');
            $('#divProductOptionCreateStep3New').remove();
        } else {
            // Is New not clone, remove clone fields
            console.debug('Is New Not clone, removing clone fields');
            $('#divProductOptionCreateStep3Clone').remove();            
        }
        // Validate Form
        var v = $('#admin_product_edit_form').valid();
        if (v === false) {
            //alert('One or more fields on the parent form did not pass validation. Please check your fields and try again.');
            return false;
        }
        var BTN = $(this);
        var oldHtml = BTN.html();
        BTN.prop('disabled', true).html('<i class=\'icon-refresh icon-white\'></i>&nbsp;Saving...');
        var go = $.ajax({
            type: 'POST',
            data: $('#admin_product_edit_form').serialize()
        })
        .done(function(results) {
            $('#theRest').addClass('hidden');
            $('#productOptionCreateResultsDynamic').html(results);
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