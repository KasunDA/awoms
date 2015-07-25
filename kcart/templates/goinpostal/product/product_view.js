
$(document).ready(function() {

    // Main Image Carousel
    var mic = $('#productCarousel');
    mic.carousel({
        interval: 4500
    });

    // Thumb Image Carousel
    var tic = $('#productThumbCarousel');
    tic.carousel({
        interval: 6750
    });

    // Thumb Image Click - Go to Frame
    $('.carouselFrame').click(function() {
        var frameNum = parseInt($(this).val(), 10);
        mic.carousel(frameNum);
    });

    // Attach children options to parent options
    function organizeOptions() {
        console.debug('// organizeOptions()');
        
        // Each .childOption
        $('.childOption').each(function() {
            // Attach to .parentOption
            var matches = this.className.match(/\b(childOfOptionID\d+)\b/);
            var parentOptionID = matches[0];
            parentOptionID = parentOptionID.replace('childOfOptionID', '');
            console.debug('Appending this .childOption to .parentOptionID: ' + parentOptionID);
            $('#optionID' + parentOptionID).append(this);
        });

    }
    organizeOptions();

    // Hidden Options
    $('.choice').on('change', fireTrigger);

    // fireTrigger: hide/show options based on choices selected
    function fireTrigger() {
        console.debug('// ------------fireTrigger------------');
        var thisChoiceID = $(this).attr('id');
        var thisChoiceName = $(this).attr('name');
            thisChoiceName = thisChoiceName.replace("[", "\\[");
            thisChoiceName = thisChoiceName.replace("]", "\\]");
        var divParentOptionID = $('#' + thisChoiceID).parents('.parentOption').attr('id');
        console.debug('// This Choice Info');
        console.debug('// ChoiceID: ' + thisChoiceID);
        console.debug('// ChoiceName: ' + thisChoiceName);
        console.debug('// ParentOptionID: ' + divParentOptionID);

        // Loop twice: Hide all unchecked, then Show all checked
        for (var loops=0; loops<2; loops++) {

            console.debug('// Each .choice[name=' + thisChoiceName + '] (siblings):');
            $('.choice[name=' + thisChoiceName + ']').each(function() {
                if (this.type === 'select-one') {
                    //console.debug(' (Type: Select)');
                    var eachID = $('.choice[name=' + thisChoiceName + ']').find('option:selected').val();
                    var eachChecked = true;
                    divParentOptionID = $(this).parents('.parentOption').attr('id');
                } else {
                    //console.debug(' (Type: Non-Select)');
                    var eachID = this.id;
                    var eachChecked = this.checked;
                    divParentOptionID = $(this).parents('.parentOption').attr('id');
                }

                // Skip checked/unchecked depending if on first/second loop
                if (loops === 0) {
                    if (eachChecked) {
                        return;
                    }
                } else {
                    if (!eachChecked) {
                        return;
                    }
                }

                // Each div triggered by this Choice ID:
                console.debug('.triggeredByChoiceID' + eachID + '.each:');
                $('.triggeredByChoiceID' + eachID).each(function() {
                    var divIDtoModify = this.id;
                    var divChildOption = $('#' + divIDtoModify);
                    console.debug('-- Div triggered by this Choice ID: divIDtoModify/divChildOption: #' + divIDtoModify);

                    // Visible
                    if (eachChecked) {
                        // console.debug('---- Checked/VISIBLE (remove hidden/disabled)');
                        console.debug('---#' + divIDtoModify + '.find(:input).REMOVE hidden/disabled');
                        divChildOption.removeClass('hidden');
                        divChildOption.find(':input').attr('disabled', false);
                    // Hidden
                    } else {
                        // console.debug('---- Unchecked/HIDDEN (add hidden/disabled)');
                        console.debug('---#' + divIDtoModify + '.find(:input).ADD hidden/disabled');
                        divChildOption.addClass('hidden');
                        divChildOption.find(':input').removeAttr('checked').attr('disabled', true); // Removes checked
                        divChildOption.find(':input:checked').change(); // Actually Fires trigger again for nested children
                        console.debug('** Hiding subchildren'); // Reset wizard
                        divChildOption.find('.childOption').addClass('hidden'); // Hide subchildren
                    }
                });
            });
        }

    }

    // Add Product to Cart Form Validation
    $('#addToCart').validate();
    
    // Default selections
    $('.clickMeOnLoad').each(function() {
        console.debug('[x] clickMeOnLoad: #' + $(this).attr('id'));
        $(this).click();
    });

});