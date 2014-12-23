
$('.toggle_file_owner').on('click', function() {

    var table = $(this).closest('table');
    var row = $(this).closest('tr');

    // Each Row
    $("tr", table).each(function() {
        // Disable inputs (not toggle checkboxes)
        $(":input:not(.toggle_file_owner)", $(this)).prop("disabled", true);

        // Uncheck toggle checkbox if checked
        $(":input.toggle_file_owner", $(this)).prop("checked", false);
    });

    // Enable selected row's inputs
    $(":input", row).prop("disabled", false);
    $(this).prop("checked", true);
});

$('.brand_choice_list').click();


var fileUpload = $('#fileUpload'); // Field or form to attach .fileupload
var divForm = $('#divFileUploadForm');
var divResults = $('#divFileUploadResults');
var divProgress = $('#divFileUploadProgress');
var divProgressBar = $('#divFileUploadProgressBar');
var divProgressRemainingBar = $('#divFileUploadProgressRemainingBar');
var txtInProgressUpload = '<strong>Uploading...</strong>';
var txtInProgressResize = '<strong>Uploaded. Please wait...</strong>';
var txtDone = '<strong>Done!</strong>';
var btnClose = $('.btnUploadClose');

// File Upload (form or [field])
var fileCount = 0;
var fileDoneCount = 0;
fileUpload.fileupload({
    dataType: 'html',
    add: function(e, data) {
        fileCount++;
        //$('#uploadModal').modal('show');

        btnClose.prop('disabled', true);
        divForm.addClass('hidden');
        divProgress.removeClass('hidden');

        data.submit();
    },
    done: function(e, data) {
        fileDoneCount++;
        if (fileDoneCount === fileCount) {
            divProgressBar.css('width', '100%').html(txtDone);
            divProgressRemainingBar.css('width', '0%');
            divProgress.removeClass('active progress-striped').addClass('progress-success');
            btnClose.prop('disabled', false);
        }
        divResults.append(data.result);
    },
    progressall: function(e, data) {
        var progress = parseInt(data.loaded / data.total * 100, 10);
        var progressRemaining = 100 - progress;
        if (progress === 100) {
            divProgressBar.html(txtInProgressResize).removeClass('bar-info').addClass('bar-success'); // Upload finished, now resizing until .done
            divProgressRemainingBar.html(' <strong>Resizing Images...</strong>').removeClass('bar-danger').addClass('bar-info');
        } else {
            divProgressBar.html(txtInProgressUpload + '<strong> (' + (fileDoneCount + 1) + '/' + fileCount + ') ' + progress + '%</strong>');
            divProgressRemainingBar.html(' <strong>Remaining: ' + progressRemaining + '%</strong>');
        }
        if (progress >= 75) {
            progress = 75; // Keeps bar from going to 100% when upload is finished as it takes time for resizing
            progressRemaining = 100 - progress;
        }
        divProgressBar.css(
                'width',
                progress + '%'
                );
        divProgressRemainingBar.css(
                'width',
                progressRemaining + '%'
                );
    }
});

// Upload Modal :: Close :: Refresh Page (if needed)
$('.btnUploadClose').click(function() {
    var refresh = divResults.html();
    if (refresh !== '') {
        location.reload();
    }
});

// Toggle text overlay form
$('#enableTextOverlay').change(function() {
    $('#divTextOverlay').toggleClass('hidden');
});
$('#enableTextOverlay').click();