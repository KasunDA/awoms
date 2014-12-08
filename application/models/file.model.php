<?php

class File extends Model
{

    protected static function getColumns()
    {
        $cols = array(
            'fileID', 'brandID', 'parentFileID', 'storeID', 'categoryID', 'productID', 'userID', 'usergroupID', 'customerID',
            'isActive', 'isDeleted', 'sortOrder', 'dateCreated', 'isPrivate', 'type', 'ext', 'imgWidth', 'imgHeight', 'imgOrient',
            'label', 'displayName'
        );
        return $cols;
    }

    public function getWhere($where = NULL, $cols = NULL, $order = NULL, $aclWhere = NULL, $in = NULL, $loadChildren = FALSE)
    {
        if ($order == NULL)
        {
            $order = "fileID, brandID, parentFileID, storeID, categoryID, productID, userID, usergroupID, customerID";
        }
        return parent::getWhere($where, $cols, $order, $aclWhere, $in, $loadChildren);
    }

    public static function LoadExtendedItem($item)
    {
        // Load linked or child items if necessary
//        $Child      = new Child();
//        $item['children'] = $Child->getWhere(array('parentID'     => $item['exampleID']));
        return $item;
    }

    /**
     * handleFileUpload
     *
     * Handles all file uploads
     *
     * @since v00.00.00
     *
     * @version v00.00.00
     *
     * @uses SimpleImage
     *
     * @param string $htmlFilesName
     * @param string  $fileType
     * @param int $brandID
     *
     * @return boolean
     *
     * @todo Sanitize POST
     */
    public static function handleFileUpload($htmlFilesName, $fileType, $brandID, $storeID = NULL, $categoryID = NULL,
                                            $productID = NULL, $userID = NULL, $usergroupID = NULL, $customerID = NULL,
                                            $parentFileID = NULL
    )
    {
        Errors::debugLogger(__METHOD__, 1);
        Errors::debugLogger(func_get_args(), 1);

        $File = new File();

        //
        // File Type
        //
        if ($fileType == 'image')
        {
            //
            // Images
            //

            // Init Image
            //$image = new Image();
            // Define text overlay if set
            if (!empty($_POST['enableTextOverlay']))
            {
                $textOverlay  = $_POST['textOverlay'];
                $fontFile     = $_POST['fontFile'];
                $fontSize     = $_POST['fontSize'];
                $fontColor    = $_POST['fontColor'];
                $fontPosition = $_POST['fontPosition'];
                $xOffset      = $_POST['xOffset'];
                $yOffset      = $_POST['yOffset'];
            }
            else
            {
                $textOverlay  = NULL;
                $fontFile     = NULL;
                $fontSize     = NULL;
                $fontColor    = NULL;
                $fontPosition = NULL;
                $xOffset      = NULL;
                $yOffset      = NULL;
            }
        }
        else
        {
            trigger_error("Unhandled upload file type", E_USER_ERROR);
            return false;
        }

        //
        // Handle each file
        //
        $returnResults = "";
        $fileCount     = count($_FILES[$htmlFilesName]['name']);
        for ($i = 0; $i < $fileCount; $i++)
        {

            //
            // Images
            //
            if ($fileType == 'image')
            {
                // Save orig image info to database
                $originalRaw = $_FILES[$htmlFilesName]['tmp_name'][$i];
                $simpleImage = new SimpleImage($originalRaw);

                $info   = $simpleImage->get_original_info();
                $ext    = $info['format'];
                $width  = $info['width'];
                $height = $info['height'];
                $orient = $info['orientation'];

                $fileID      = "DEFAULT"; // @TODO: New or Existing/Replace?
                $isActive    = 1;
                $isDeleted   = 0;
                $isPrivate   = 0;
                $dateCreated = Utility::getDateTimeUTC();
                $sortOrder   = NULL;
                $label       = $_FILES[$htmlFilesName]['name'][$i];
                $displayName = str_replace("." . substr($label, strrpos($label, '.') + 1), "", $label);

                $data = array(
                    'fileID'       => $fileID,
                    'brandID'      => $brandID,
                    'parentFileID' => $parentFileID,
                    'storeID'      => $storeID,
                    'categoryID'   => $categoryID,
                    'productID'    => $productID,
                    'userID'       => $userID,
                    'usergroupID'  => $usergroupID,
                    'customerID'   => $customerID,
                    'isActive'     => $isActive,
                    'isDeleted'    => $isDeleted,
                    'sortOrder'    => $sortOrder,
                    'dateCreated'  => $dateCreated,
                    'isPrivate'    => $isPrivate,
                    'type'         => $fileType,
                    'ext'          => $ext,
                    'imgWidth'     => $width,
                    'imgHeight'    => $height,
                    'imgOrient'    => $orient,
                    'label'        => $label,
                    'displayName'  => $displayName
                );

                $fileID = $File->update($data);

                #$imageID = $image->saveImageInfo($cartID, $imageID, $parentImageID, $active, $name, $ext, $width, $height, $orient,
                #                                $userID, $customerID, $categoryID, $productID, $sortOrder, $imageVisibility); ////////////////////////////////////////////////////////


                $returnResults .= "
                    <table class='table table-bordered'>
                        <tr>
                            <td class='span2'>
                                Saved original:<br /> $width x $height
                            </td>
                            <td class='span6'>
                                <img src='" . cartPublicUrl . "getfile.php?cartID=" . $cartID . "&imageID=" . $imageID . "&w=" . $width . "&h=" . $height . "' />
                            </td>
                        </tr>";

                // Make nested image folder if not exist
                $destPath = ROOT . DS . 'files' . DS . $brandID . DS . $imageID;
                Utility::createNestedDirectory($destPath);

                // Where to save original image
                $origImgPath = $destPath . '/' . $imageID . '.' . $width . 'x' . $height . '.' . $ext;

                // If file already exists, rename (safe delete) existing file
                if (file_exists($origImgPath))
                {
                    $j = 0;
                    while (TRUE)
                    {
                        $renameFile = $destPath . '/' . $imageID . '.' . $width . 'x' . $height . '.' . $j . '.' . $ext;
                        if (file_exists($renameFile))
                        {
                            $j++;
                            continue;
                        }
                        if (!rename($origImgPath, $renameFile))
                        {
                            trigger_error('Could not rename existing file (j:' . $j . '): ' . $origImgPath, E_USER_NOTICE);
                            return false;
                        }
                        break;
                    }
                }

                // Save original image
                $failedToCreateImage = FALSE;
                if (!empty($textOverlay))
                {
                    // Add text overlay
                    if (!$image->createImage($originalRaw, $origImgPath, $width, $height, $textOverlay, $fontFile, $fontSize,
                                             $fontColor, $fontPosition, $xOffset, $yOffset))
                    {
                        $failedToCreateImage = TRUE;
                    }
                }
                else
                {
                    // Move raw uploaded file from tmp
                    if (!move_uploaded_file($originalRaw, $origImgPath))
                    {
                        $failedToCreateImage = TRUE;
                    }
                    else
                    {
                        $originalRaw = $origImgPath;
                    }
                }
                if ($failedToCreateImage === TRUE)
                {
                    // Set image to inactive in database if fails
                    $image->setImageActive($cartID, $imageID, 0);
                    trigger_error('Could not save orig file: ' . $originalRaw, E_USER_NOTICE);
                    return false;
                }

                /*
                 * Create new sizes if selected
                 */
                if (!empty($_POST['size']))
                {
                    foreach ($_POST['size'] as $sizeReq)
                    {
                        // Only creates max size allowed and doesnt duplicate same size
                        if (($sizeReq > $height) && ($sizeReq > $width))
                        {
                            $returnResults .= '<tr><td><cite>Skipped:</cite></td><td>' . $sizeReq . ' x ' . $sizeReq . '</td></tr>';
                            continue;
                        }
                        // Load original image data to get best fit for new size
                        $origSimpleImage = new SimpleImage($originalRaw);
                        $origSimpleImage->best_fit($sizeReq, $sizeReq);
                        $bfW             = $origSimpleImage->get_width();
                        $bfH             = $origSimpleImage->get_height();
                        // Save new image size info to db
                        $parentImageID   = $imageID;
                        $userID          = NULL;
                        $customerID      = NULL;
                        $categoryID      = NULL;
                        $productID       = NULL;
                        $sortOrder       = NULL;
                        $visibility      = NULL;
                        $subImageID      = $image->saveImageInfo($cartID, 'DEFAULT', $imageID, $active, $name, $ext, $bfW, $bfH,
                                                                 $orient, $userID, $customerID, $categoryID, $productID, $sortOrder,
                                                                 $visibility);
                        $newImg          = $destPath . '/' . $imageID . '.' . $bfW . 'x' . $bfH . '.' . $ext;
                        // If file already exists, rename existing file
                        if (file_exists($newImg))
                        {
                            $z = 0;
                            while (TRUE)
                            {
                                $renameFile = $destPath . '/' . $imageID . '.' . $bfW . 'x' . $bfH . '.' . $z . '.' . $ext;
                                if (file_exists($renameFile))
                                {
                                    $z++;
                                    continue;
                                }
                                if (!rename($newImg, $renameFile))
                                {
                                    trigger_error('Could not rename existing file (z:' . $z . '): ' . $newImg, E_USER_NOTICE);
                                    return false;
                                }
                                break;
                            }
                        }
                        $returnResults .= "<tr>
                                <td>Saved resized: $bfW x $bfH</td>
                                <td>
                                    <img src='" . cartPublicUrl . "getfile.php?cartID=" . $cartID . "&imageID=" . $subImageID . "&w=" . $bfW . "&h=" . $bfH . "' />
                                </td>
                            </tr>";
                        // Create resized image (best_fit)
                        $image->createImage($origImgPath, $newImg, $bfW, $bfH, $textOverlay, $fontFile, $fontSize, $fontColor,
                                            $fontPosition, $xOffset, $yOffset);
                    }
                }
                $returnResults .= "</table>";
            }
        }

        return $returnResults;
    }

    /*
     * Recursive tree builder (no touchy)
     */

    public static function buildTree($tree_array, $is_sub = FALSE, $olClass = FALSE, $treeTitle = FALSE)
    {
        $tree = "\n<ol id='menutree'>\n"; // Open the tree container
        if ($olClass !== FALSE)
        {
            $tree = "\n<ol class='" . $olClass . "'>\n";
        }

        if (!empty($treeTitle))
        {
            $tree .= "<li class='heading'>" . $treeTitle . "</li>";
        }

        /*
         * Loop through the array to extract element values
         */
        foreach ($tree_array as $id => $properties)
        {

            /*
             * Because each page element is another array, we
             * need to loop again. This time, we save individual
             * array elements as variables, using the array key
             * as the variable name.
             */
            foreach ($properties as $key => $val)
            {

                /*
                 * If the array element contains another array,
                 * call the buildtree() function recursively to
                 * build the sub-tree and store it in $sub
                 */
                if (is_array($val))
                {
                    $sub = self::buildtree($val, TRUE);
                }

                /*
                 * Otherwise, set $sub to NULL and store the
                 * element's value in a variable
                 */
                else
                {
                    $sub  = NULL;
                    $$key = $val;
                }
            }

            /*
             * If no array element had the key 'url', set the
             * $url variable equal to the containing element's ID
             */
            if (!isset($url))
            {
                $url = $id;
            }

            /*
             * Use the created variables to output HTML
             */

            /*
             * If the supplied array is part of a sub-tree, add the sub-tree class
             */
            $attr        = "";
            $folderBegin = "";
            $folderEnd   = "";
            if (!empty($sub))
            {
                $attr = " class='has-sub'";

                $folderBegin = "
                    <!-- Folder: -->
                    <label class='menu_label' for='" . $id . "'>(" . $id . ") " . $display . "</label>
                    <input type='checkbox' id='" . $id . "' />

                    <!-- Children: -->
                    <ol>
                    ";
            }
            else
            {
                $folderEnd = "</ol>";
            }
            $tree .= "\t\t<li$attr>\n\t" . $folderBegin . "\t\t<a href='$url'>$display</a>$sub\n\t\t</li>\n";

            /*
             * Destroy the variables to ensure they're reset
             * on each iteration
             */
            unset($url, $display, $sub);
        }

        $tree .= $folderEnd;

        /*
         * Close the tree container and return the markup for output
         */
        return $tree . "</ol>\n";
    }

}