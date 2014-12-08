$(document).ready(function() {

    // Admin :: Product :: Privacy help button
    $('#productPrivacyHelpModalBtn').click(function() {
        $('#productPrivacyHelpModal').toggleClass('hidden');
    });

    // Admin :: Product :: Privacy help button
    $('#productTaxableHelpModalBtn').click(function() {
        $('#productTaxableHelpModal').toggleClass('hidden');
    });

    // Admin :: Product :: Remove Product Image
    $('.btnRemoveImg').click(function() {
        var r = confirm('Are you sure you want to permanently delete this image?');
        if (r === false) {
            return false;
        }
        var go = $.ajax({
            type: 'POST',
            data: {m: 'ajax',
                cartID: $('#productCartID').val(),
                productID: $('#productID').val(),
                imageID: $(this).val(),
                a: 'remove_product_image'}
        })
        .done(function(results) {
            window.location.reload();
        })
        .fail(function(msg) {
            alert('Error:' + msg);
        })
        .always(function() {
        });
    });

    // Admin :: Product :: Toggle Image Carousel Visibility
    $('.showInCarousel').change(function() {
        console.debug('showInCarousel change:');
        var imgID = $(this).val();
        if (this.checked) {
            var showInCarousel = 1;
            
            $('input:checkbox.showInCarouselThumbs').each(function(){
                var thisCheckbox = $(this);
                if (thisCheckbox.val() === imgID) {
                    thisCheckbox.prop('disabled', false);
                }
              });
        } else {
            var showInCarousel = 0;
            $('input:checkbox.showInCarouselThumbs').each(function(){
                var thisCheckbox = $(this);
                if (thisCheckbox.val() === imgID) {
                    thisCheckbox.prop('checked', false).prop('disabled', true);
                }
              });
        }
        console.debug('show: ' + showInCarousel);
        var GO = $.ajax({
            type: 'POST',
            data: {m: 'ajax',
                cartID: $('#productCartID').val(),
                productID: $('#productID').val(),
                imageID: imgID,
                showInCarousel: showInCarousel,
                a: 'setImageVisibility'}
        })
        .done(function(results) {
            // window.location.reload();
            console.debug(results);
        })
        .fail(function(msg) {
            alert('Error:' + msg);
        })
        .always(function() {
        });
    });
    
    // Admin :: Product :: Toggle Image Carousel Thumbs Visibility
    $('.showInCarouselThumbs').change(function() {
        console.debug('showInCarouselThumbs change:');
        var imgID = $(this).val();
        if (this.checked) {
            var showInCarouselThumbs = 1;
        } else {
            var showInCarouselThumbs = 0;
        }
        console.debug('show: ' + showInCarouselThumbs);
        var GO = $.ajax({
            type: 'POST',
            data: {m: 'ajax',
                cartID: $('#productCartID').val(),
                productID: $('#productID').val(),
                imageID: imgID,
                showInCarouselThumbs: showInCarouselThumbs,
                a: 'setImageVisibility'}
        })
        .done(function(results) {
            // window.location.reload();
            console.debug(results);
        })
        .fail(function(msg) {
            alert('Error:' + msg);
        })
        .always(function() {
        });
    });

    // Admin :: Product :: Move Product Image Up or Down
    $('.btnMoveUpImg, .btnMoveDownImg').click(function() {
        var imgID = $(this).val();
        var oldOrder = $('#imgSortOrder' + imgID).val();
        if ($(this).hasClass('btnMoveUpImg')) {
            var newOrder = parseInt(oldOrder) - 1;
        } else {
            var newOrder = parseInt(oldOrder) + 1;
        }
        console.debug('Move imgID: ' + imgID + ' from: ' + oldOrder + ' to: ' + newOrder);
        var go = $.ajax({
            data: {m: 'ajax',
                productID: $('#productID').val(),
                imageID: imgID,
                oldOrder: oldOrder,
                newOrder: newOrder,
                a: 'moveImg'}
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

    // Admin :: Product :: Edit product form validation
    $('#admin_product_edit_form').validate({
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

    // Admin :: Product :: Edit Product :: Save
    $('#btnSaveProduct').click(function() {
        var v = $('#admin_product_edit_form').valid();
        if (v === false) {
            alert('One or more fields on the parent form did not pass validation. Please check your fields and try again.');
            return false;
        }
        console.debug('Saving product');
        var BTN = $(this);
        var oldHtml = BTN.html();
        BTN.prop('disabled', true).html('<i class=\'icon-refresh icon-white\'></i>&nbsp;Saving...');
        var go = $.ajax({
            type: 'POST',
            data: $('#admin_product_edit_form').serialize()
        })
        .done(function(results) {
            $('#divProductResults').html(results);
            //delayedRefresh();
        })
        .fail(function(msg) {
            console.debug(msg);
            alert('Error: ' + msg);
        })
        .always(function() {
        });
    });
});