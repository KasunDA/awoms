$(document).ready(function() {
    
    // Admin :: Products :: Option :: Remove Option
    $('.removeOptionBtn').click(function() {
        var r = confirm('Are you sure you want to permanently delete this option AND all of its choices?');
        if (r == false) {
            return false;
        }
        var go = $.ajax({
            data: {m: 'ajax',
                productID: $('#productID').val(),
                optionID: $(this).val(),
                a: 'removeProductOption'}
        })
        .done(function(results) {
            $('#divProductResults').html(results);
            delayedRefresh(0);
        })
        .fail(function(msg) {
            console.debug(msg);
        })
        .always(function() {
        });
    });
    
    // Admin :: Products :: Remove Option Choice
    $('.removeChoiceBtn').click(function() {
        var r = confirm('Are you sure you want to permanently delete this choice?');
        if (r == false) {
            return false;
        }
        console.debug('Remove ChoiceID: ' + $(this).val());
        var go = $.ajax({
            data: {m: 'ajax',
                productID: $('#productID').val(),
                choiceID: $(this).val(),
                a: 'removeProductOptionChoiceCustom'}
        })
        .done(function(results) {
            $('#divProductResults').html(results);
            delayedRefresh(0);
        })
        .fail(function(msg) {
            console.debug(msg);
        })
        .always(function() {
        });
    });

    // Admin :: Products :: Add Option Choice
    $('.addOptionDetailBtn').click(function() {
        var optionIDCustom = $(this).val();
console.debug('Add choice to optionIDCustom: ' + optionIDCustom);
        var GO = $.ajax({
            data: {m: 'ajax',
                cartID: $('#productCartID').val(),
                productID: $('#productID').val(),
                optionIDCustom: optionIDCustom,
                a: 'addProductOptionChoiceCustom'}
        })
        .done(function(results) {
            $('#tblOptionID' + optionIDCustom + ' tr:last').after(results);
        })
        .fail(function(msg) {
            console.debug(msg);
        })
        .always(function() {
        });
    });
    
    // Admin :: Products :: Option :: Move Option Up/Down
    $('.moveOptionUp, .moveOptionDown').click(function() {
        var optionIDCustom = $(this).val();
        var oldOrder = $('#option' + optionIDCustom + 'SortOrder').val();
        if ($(this).hasClass('moveOptionUp')) {
            var newOrder = parseInt(oldOrder) - 1;
        } else {
            var newOrder = parseInt(oldOrder) + 1;
        }
        console.debug('Move optionID: ' + optionIDCustom + ' from: ' + oldOrder + ' to: ' + newOrder);
        var go = $.ajax({
            data: {m: 'ajax',
                productID: $('#productID').val(),
                optionIDCustom: optionIDCustom,
                oldOrder: oldOrder,
                newOrder: newOrder,
                a: 'moveOption'}
        })
        .done(function(results) {
            $('#divProductResults').html(results);
            delayedRefresh(0);
        })
        .fail(function(msg) {
            console.debug(msg);
        })
        .always(function() {
        });
    });
    
    // Admin :: Products :: Option :: Choice :: Move Choice Up/Down
    $('.moveChoiceUp, .moveChoiceDown').click(function() {
        var optionChoiceInfo = $(this).attr('name');
        var info = optionChoiceInfo.split("|||");
        var optionIDCustom = info[0];
        var choiceIDCustom = info[1];
        var oldOrder = $(this).val();
        if ($(this).hasClass('moveChoiceUp')) {
            var newOrder = parseInt(oldOrder) - 1;
        } else {
            var newOrder = parseInt(oldOrder) + 1;
        }
        console.debug('Move choiceID: ' + choiceIDCustom + ' from: ' + oldOrder + ' to: ' + newOrder);
        var go = $.ajax({
            data: {m: 'ajax',
                optionIDCustom: optionIDCustom,
                choiceIDCustom: choiceIDCustom,
                oldOrder: oldOrder,
                newOrder: newOrder,
                a: 'moveChoice'}
        })
        .done(function(results) {
            $('#divProductResults').html(results);
            delayedRefresh(0);
        })
        .fail(function(msg) {
            console.debug(msg);
        })
        .always(function() {
        });
    });
    
    //
    // Add Image Choice (click)
    //
    $('.addImageChoiceBtn').click(function() {
        console.debug('images to choose from: ');
        var options = $.ajax({
            data: {m: 'ajax',
                cartID: $('#productCartID').val(),
                productID: $('#productID').val(),
                a: 'getProductImagesAddChoiceModal'}
        })
        .done(function(results) {
            console.log('Results...');
            $('#addImageChoiceResults').html(results);
        })
        .fail(function(msg) {
            console.debug(msg);
        })
        .always(function() {
        });
    });
    
    // Parent Option Configure Modal (click)
    $('.configureOption').click(function() {
        var optionID = $(this).val();
        $('#configureOptionID').val(optionID);
        console.debug('configureOption click, optionID: ' + optionID);
        $('#editOptionModal').modal('show');
        $('#productOptionConfigureResultsDynamic').html('Option ID: ' + optionID);
        var options = $.ajax({
            data: {m: 'ajax',
                cartID: $('#productCartID').val(),
                productID: $('#productID').val(),
                optionID: optionID,
                a: 'getOptionModifyForm'}
        })
        .done(function(results) {
            console.log('Results...');
            $('#productOptionConfigureResultsDynamic').html(results);
        })
        .fail(function(msg) {
            console.debug(msg);
        })
        .always(function() {
        });
    });

    // Parent Option Configure Modal Save (click)
    $('#configureProductOptionBtn').click(function() {
        var optionID = $('#configureOptionID').val();
        // New req/behavior values
        var newReqVal = $("input[name='configureOptionRequiredNew']:checked").val();
        var newBehVal = $("input[name='configureOptionBehaviorNew']:checked").val();
        console.debug('Setting optionID ' + optionID + ' new required value of ' + newReqVal);
        $('#option' + optionID + 'Required').val(newReqVal);
        if (typeof newBehVal !== 'undefined') {
            console.debug('Setting optionID ' + optionID + ' new behavior value of ' + newBehVal);
            $('#option' + optionID + 'Behavior').val(newBehVal);
        }

        // Save Product
        $('#btnSaveProduct').click();

        // Close modal
        $('#configureProductOptionCloseBtn').click();
    });

});