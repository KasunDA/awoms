<div id="uploadModal">

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
        if (empty($fileType) || $fileType == 'image')
        {

            // NEW file form
            #if ($fileID == 'DEFAULT')
            #{
            ?>

            <form id='<?php echo $formID; ?>'  method='POST'>
                <input type='hidden' name='step' value='2' />
                <input type='hidden' name='inp_fileID' value='<?php echo $fileID; ?>' />

                <h1>File Information</h1>
                <table class="bordered">

                    <!-- Brand -->
                    <tr class='<?php echo $brandChoiceListClass; ?>'>
                        <td>
                            <label>
                                <input type='checkbox' class='toggle_file_owner brand_choice_list'/>
                                Brand
                            </label>
                        </td>
                        <td>
                            <select name='inp_brandID'>
                                <?= $brandChoiceList; ?>
                            </select>
                        </td>
                    </tr>

                    <!-- Store -->
                    <tr class='<?php echo $storeChoiceListClass; ?>'>
                        <td>
                            <label>
                                <input type='checkbox' class='toggle_file_owner'/>
                                Store
                            </label>
                        </td>
                        <td>
                            <select name='inp_storeID'>
                                <?= $storeChoiceList; ?>
                            </select>
                        </td>
                    </tr>

                    <!-- Group -->
                    <tr class='<?php echo $usergroupChoiceListClass; ?>'>
                        <td>
                            <label>
                                <input type='checkbox' class='toggle_file_owner'/>
                                Group
                            </label>
                        </td>
                        <td>
                            <select name='inp_usergroupID'>
                                <?= $usergroupChoiceList; ?>
                            </select>
                        </td>
                    </tr>

                    <!-- User -->
                    <tr class='<?php echo $userChoiceListClass; ?>'>
                        <td>
                            <label>
                                <input type='checkbox' class='toggle_file_owner'/>
                                User
                            </label>
                        </td>
                        <td>
                            <select name='inp_userID'>
                                <?= $userChoiceList; ?>
                            </select>
                        </td>
                    </tr>


                    <table class='bordered'>
                        <tr>
                            <th colspan='2'>
                                Thumbnail Sizes to pre-create:
                            </th>
                        </tr>
                        <tr>
                            <td style="width:420px"><p>Optional sizes to create from original image, dimensions larger than the original will be ignored to prevent
                                    distortion. Useful for generating smaller thumbnail sizes to speed up page load times.</p></td>
                            <td>
                                <label style="cursor: pointer">
                                    <input type='checkbox' id='size1' name='size[]' value='64' checked /> Icon (64x64)<br />
                                </label>
                                <label style="cursor: pointer">
                                    <input type='checkbox' id='size2' name='size[]' value='128' checked /> Thumb (128x128)<br />
                                </label>
                                <label style="cursor: pointer">
                                    <input type='checkbox' id='size3' name='size[]' value='256' checked /> Small (256x256)<br />
                                </label>
                                <label style="cursor: pointer">
                                    <input type='checkbox' id='size4' name='size[]' value='384' checked /> Medium (384x384)<br />
                                </label>
                                <label style="cursor: pointer">
                                    <input type='checkbox' id='size5' name='size[]' value='512' checked /> Large (512x512)<br />
                                </label>
                                <label style="cursor: pointer">
                                    <input type='checkbox' id='size6' name='size[]' value='768' checked /> X-Large (768x768)<br />
                                </label>
                            </td>
                        </tr>
                    </table>
                    <table class='bordered'>
                        <tr>
                            <th>
                                <label style="cursor: pointer">
                                    <input type='checkbox' id='enableTextOverlay' name='enableTextOverlay' value='1' /> Add Text Watermark
                                </label>
                            </th>
                        </tr>
                        <tr>
                            <td><p>Optional text watermark can be added to images on upload. Useful to prevent image theft.</p></td>
                        </tr>

                        <tr>
                            <td>

                                <div id='divTextOverlay' class='hidden'>
                                    <table style="margin: 0 auto;">
                                        <tr>
                                            <td>Text:
                                                <a href="#" class="tooltip-on" data-toggle="tooltip" data-placement='right' title="For the &#169; symbol use the following: &amp;#169;">
                                                    <i class="icon-question-sign"></i>
                                                </a>
                                            </td>
                                            <td style="width:300px">
                                                <input type='text' name='textOverlay' value='&#169; <?php echo $_SESSION['brand']['brandName']; ?>' size="40"/>
                                            </td>
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
                            </td>
                        </tr>
                    </table>

                    <div class='alert info'>
                        <p><i><strong>Note:</strong> upload will begin as soon as images are selected, do NOT close or refresh your browser</i></p>
                        <!-- Image selector (allows multiple) -->
                        <p><input id="fileUpload" name="files[]" accept="image/*" type="file" multiple /></p>
                    </div>

            </form>

            <?php
            #} else { # Update form
        }
        ?>

    </div>

</div>

<?php
$pageJavaScript[] = file_get_contents(ROOT . DS . "application" . DS . "views" . DS . "files" . DS . "filesUpload.js");