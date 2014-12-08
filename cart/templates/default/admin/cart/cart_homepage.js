$(document).ready(function() {

$('.chosenSlideImage').click(function() {
  if ($(this).css("border-width") === '4px')
  {
    var newImageID = null;
    var classList = $(this).attr('class').split(/\s+/);
    $.each( classList, function(index, item){
      var parts = item.split(/(^existingImageID)(\d+)/);
      if (typeof(parts[1]) === 'undefined') {
        return;
      }
      newImageID = parts[2];
    });

    var oid = $('#origImgID').val();
    //alert ('Selected ' + newImageID + '! Orig: ' + oid);
    
    var slnm = oid.split(/(^storefrontSlide)(\d+)/);
    var slideNum = slnm[2];
    
    // Post, replace slide #, orig image #, new image #
    var go = $.ajax({
        type: 'POST',
        data: {m: 'ajax',
            cartID: $('#cartID').val(),
            qa: 'storefront_carousel_slide',
            slideNum: slideNum,
            newImageID: newImageID}
    })
    .done(function(results) {
        console.debug(results);
        window.location.reload();
    })
    .fail(function(msg) {
        console.debug(msg);
    })
    .always(function() {
    });
  }
  
  $('.chosenSlideImage').each(function() {
    $(this).css({ "border": "1px solid #ccc" });
  });
  
  $(this).css({ "border": "4px solid blue" });
});

// Admin :: Cart :: Edit Image (click)
$('.carouselSlideImg').click(function() {
    console.debug('clicked');
    // Orig Image
    var origImgID = $(this).attr('id');
    console.debug('Edit image, origID: ' + origImgID);
    $('img#' + origImgID).removeClass('hidden');

    // Replacement Image Upload Modal
    $('#uploadModalBody').prepend('<h4>Double-click an image to select</h4>');
    //$('#uploadModalBody').prepend($('img#' + origImgID));
    //$('#uploadModalBody').prepend("<h4>Original Image</h4><input type='hidden' name='origImgID' value='" + origImgID + "' />");
    $('#uploadModalBody').prepend("<input type='hidden' id='origImgID' name='origImgID' value='" + origImgID + "' />");
    $('#uploadModal').modal('show');
});

// Admin :: Cart :: Carousel
$('.saveCarouselSettings').click(function() {
  var go = $.ajax({
      type: 'POST',
      data: $('#frm_admin_edit_cart_homepage').serialize()          
  })
  .done(function(results) {
      console.debug(results);
      alert('Saved! Click OK to reload the page.');
      window.location.reload();
  })
  .fail(function(msg) {
      //alert('Error:' + msg);
      console.debug(msg);
  })
  .always(function() {
  });
});

});