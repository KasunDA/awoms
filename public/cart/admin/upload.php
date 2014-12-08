<?php
// Security check: Deny direct file access; must be loaded through index getfile
if (count(get_included_files()) == 1) {
    header("Location: index.php");
    die("403");
}
?>
<?php
//<!-- Button to trigger modal -->
//<a href="#uploadModal" role="button" class="btn" data-toggle="modal"><i class='icon-plus'></i>&nbsp;File Upload</a>
?>
<div id="uploadModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="uploadModalLbl" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close btnUploadClose" data-dismiss="modal" aria-hidden="true">&times</button>
        <h3 id="uploadModalLbl"><?php echo $uploadTitle; ?></h3>
    </div>
    <div id='uploadModalBody' class="modal-body">

        <!-- Upload Progress -->
        <div id='divFileUploadProgress' class='progress progress-striped active hidden'>
            <div id='divFileUploadProgressBar' class="bar bar-info" style="width: 0%;"></div>
            <div id='divFileUploadProgressRemainingBar' class="bar bar-danger" style="width: 100%;"></div>
        </div>
        <div class='clearfix'></div>
        
        <!-- Upload Results -->
        <div id='divFileUploadResults'></div>
        <div class='clearfix'></div>

        <!-- Upload Form -->
        <div id='divFileUploadForm'>
            
        <?php
        if ($fileType == 'image') {
        ?>
            <table class='table table-bordered'>
                <tr>
                    <th colspan='2'>
                        Sizes to pre-create:
                        <a href="#" class="tooltip-on" data-toggle="tooltip" data-placement='right' title="Optional, but recommended to leave all sizes checked.">
                            <i class="icon-question-sign"></i>
                        </a>
                    </td>
                </tr>
                <tr>
                    <td class='span4'>Optional sizes to create from original image, dimensions larger than the original will be ignored to prevent
                        distortion. Useful for generating smaller thumbnail sizes to speed up page load times.</td>
                    <td>
                        <input type='checkbox' id='size1' name='size[]' value='64' checked /> Icon (64x64)<br />
                        <input type='checkbox' id='size2' name='size[]' value='128' checked /> Thumb (128x128)<br />
                        <input type='checkbox' id='size3' name='size[]' value='256' checked /> Small (256x256)<br />
                        <input type='checkbox' id='size4' name='size[]' value='384' checked /> Medium (384x384)<br />
                        <input type='checkbox' id='size5' name='size[]' value='512' checked /> Large (512x512)<br />
                        <input type='checkbox' id='size6' name='size[]' value='768' checked /> X-Large (768x768)<br />
                    </td>
                </tr>
            </table>
            <table class='table table-bordered'>
                <tr>
                    <th colspan='2'>
                        <input type='checkbox' id='enableTextOverlay' name='enableTextOverlay' value='1' /> Add Text Watermark
                        <a href="#" class="tooltip-on" data-toggle="tooltip" title="Optional text watermark can be added to images on upload. Useful to prevent image theft.">
                            <i class="icon-question-sign"></i>
                        </a>
                    </td>
                </tr>
            </table>

            <div id='divTextOverlay' class='hidden'>
                <table class='table table-bordered table-condensed'>
                    <tr>
                        <td class='span4'>Text:
                            <a href="#" class="tooltip-on" data-toggle="tooltip" data-placement='right' title="For the &#169; symbol use the following: &amp;#169;">
                                <i class="icon-question-sign"></i>
                            </a>
                        </td>
                        <td><input type='text' name='textOverlay' value='&#169; <?php echo $_SERVER['HTTP_HOST']; ?>' /></td>
                    </tr>
                    <tr>
                        <td>
                            Font File:
                            <a href="#" class="tooltip-on" data-toggle="tooltip" data-placement='right' title="Default options: ARIAL.TTF, ONYXN_0.TTF, PRISTINA.TTF, RAGE.TTF. Custom font files must be present in [cartPublicUrl/css] folder.">
                                <i class="icon-question-sign"></i>
                            </a>
                        </td>
                        <td><input type='text' name='fontFile' value='RAGE.TTF' /></td>
                    </tr>
                    <tr>
                        <td>Font Size:</td>
                        <td><input type='text' name='fontSize' value='8' /></td>
                    </tr>
                    <tr>
                        <td>Font Color:</td>
                        <td><input type='text' name='fontColor' value='#999999' /></td>
                    </tr>
                    <tr>
                        <td>
                            Text Position:
                            <a href="#" class="tooltip-on" data-toggle="tooltip" title="Where to put text: top, bottom, left, right, top right, bottom left, etc.">
                                <i class="icon-question-sign"></i>
                            </a>
                        </td>
                        <td><input type='text' name='fontPosition' value='top right' /></td>
                    </tr>
                    <tr>
                        <td>X Offset:</td>
                        <td><input type='text' name='xOffset' value='-2' /></td>
                    </tr>
                    <tr>
                        <td>Y Offset:</td>
                        <td><input type='text' name='yOffset' value='0' /></td>
                    </tr>
                </table>
            </div>
            
            <div class='alert alert-block alert-info'>
                <i><strong>Note:</strong> upload will begin as soon as images are selected, do NOT close or refresh your browser</i><br />
                <!-- Image selector (allows multiple) -->
                <input id="fileUpload" name="files[]" accept="image/*" type="file" multiple />
            </div>            
        <?php
        }
        ?>
            
        </div>

    </div>

    <!-- Buttons -->
    <div class="modal-footer">
        <button class="btn btnUploadClose" data-dismiss="modal" aria-hidden="true">Close</button>
    </div>
</div>
    
<?php
$pageJavaScript[] = "
<script type='text/javascript'>
    $(document).ready(function() {
    
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
            add: function (e, data) {
                fileCount++;
                $('#uploadModal').modal('show');
                btnClose.prop('disabled', true);
                divForm.addClass('hidden');
                divProgress.removeClass('hidden');
                data.submit();
            },
            done: function (e, data) {
                fileDoneCount++;
                if (fileDoneCount == fileCount) {
                    divProgressBar.css('width', '100%').html(txtDone);
                    divProgressRemainingBar.css('width', '0%');
                    divProgress.removeClass('active progress-striped').addClass('progress-success');
                    btnClose.prop('disabled', false);
                }
                divResults.append(data.result);
            },
            progressall: function (e, data) {
                var progress = parseInt(data.loaded / data.total * 100, 10);
                var progressRemaining = 100 - progress;
                if (progress == 100) {
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
            if (refresh != '') {
                location.reload();
            }
        });
        
        // Toggle text overlay form
        $('#enableTextOverlay').change(function() {
            $('#divTextOverlay').toggleClass('hidden');
        });
        $('#enableTextOverlay').click();
    });
</script>";