<?php
// Security check: Deny direct file access; must be loaded through index getfile
if (count(get_included_files()) == 1) {
    header("Location: index.php");
    die("403");
}
?>

<?php
//
// ACL Check: Read Access
//
if (empty($cartACL['read'])
        && empty($globalACL['read'])
) {
    trigger_error('Security alert.', E_USER_ERROR);
    return false;
}
//
// ACL Check: Cart limits
//
if ($_SESSION['groupID'] == 1) { // Global Admin
    $cartToGet = NULL; // All
} else {
    $cartToGet = $_SESSION['cartID']; // This cart only
}
?>

<?php
///////////////////
// BEGIN: Ajax call 
///////////////////
if (isset($_REQUEST['m'])
        && ($_REQUEST['m'] == 'ajax')) {

    //
    // Product List
    //
    if (isset($_REQUEST['a'])
            && $_REQUEST['a'] == 'productList') {
        
        // Init Product / Category
        $product      = new killerCart\Product();
        $category     = new killerCart\Product_Category();
        // Get Active Filter
        if ($_REQUEST['active'] == 3) {
            // None -> Default to ALL
            $active = 'ALL';
        } elseif ($_REQUEST['active'] == 2) {
            // All
            $active = 'ALL';
        } elseif ($_REQUEST['active'] == 1) {
            // Active
            $active = 'ACTIVE';
        } elseif ($_REQUEST['active'] == 0) {
            // Inactive
            $active = 'INACTIVE';
        } else {
            // Shouldnt get here, but assume ALL
            $active = 'ALL';
        }
        // Get all products
        $orderBy = "(SELECT pc.categoryName FROM productCategories AS pc WHERE pc.categoryID = p.categoryID), p.productName";
        if (empty($_REQUEST['filter'])
                && empty($_REQUEST['categoryID'])) {
            $productIDs   = $product->getProductIDs($active, $cartToGet, NULL, $orderBy);
        } else {
            // Filtered
            if (!empty($_REQUEST['filter'])) {
                $customWhere = "
                    (p.productName LIKE '%".$_REQUEST['filter']."%'
                    OR
                    p.productSKU LIKE '%".$_REQUEST['filter']."%')";
            } else {
                $customWhere = '';
            }
            $productIDs   = $product->getProductIDs($active, $cartToGet, $_REQUEST['categoryID'], $orderBy, NULL, $customWhere);
            // @todo full text, array of words to MATCH AGAINST p.productDescriptionPrivate LIKE '%". json_encode(filter_var($_REQUEST['filter'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW)) ."%' OR 
            // @todo full text, array of words to MATCH AGAINST p.productDescriptionPublic LIKE '%". json_encode(filter_var($_REQUEST['filter'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW)) ."%'
        }
        
        if (empty($productIDs)) {
            // Actually no products yet
            if (empty($_REQUEST['filter'])) {
                echo "
                    <div class='alert alert-block alert-info span3 offset4'>
                        <button type='button' class='close' data-dismiss='alert'>&times;</button>
                        <h4><i class='icon-info-sign'></i>&nbsp;Sorry!</h4>
                        Please add a product to begin
                    </div>
                    <div class='clearfix'></div>";
            } else {
                // No search results
                echo "
                    <div class='alert alert-block alert-info span3 offset4'>
                        <button type='button' class='close' data-dismiss='alert'>&times;</button>
                        <h4><i class='icon-info-sign'></i>&nbsp;Sorry!</h4>
                        No search results found for <strong>".htmlentities($_REQUEST['filter'])."</strong>!
                    </div>
                    <div class='clearfix'></div>";
            }
            // End
            return;
        } else {
            //
            // Search Results
            //
            $allProdNames = array();
            $allProdSKUs  = array();
            foreach ($productIDs as $prod) {
                $allProdNames[] = json_encode($prod['productName']); // Name
                if (!empty($prod['productSKU'])) {
                    $allProdSKUs[] = json_encode($prod['productSKU']); // Code
                }
                #if (!empty($prod['productDescriptionPrivate'])) {
                #$allProdDescPriv[] = json_encode($prod['productDescriptionPrivate']); // Private Description
                #}
                #if (!empty($prod['productDescriptionPublic'])) {
                #$allProdDescPub[] = json_encode($prod['productDescriptionPublic']); // Public Description
                #}
            }
            $searchListProdNames = implode(", ", $allProdNames);
            $searchListProdSKUs  = implode(", ", $allProdSKUs);
            #$searchListProdDescPriv = implode(", ", $allProdDescPriv);
            #$searchListProdDescPub  = implode(", ", $allProdDescPub);
            $finalSearchList     = $searchListProdNames . "," . $searchListProdSKUs; # . "," . $searchListProdDescPriv . "," . $searchListProdDescPub;
            $pageJavaScript = "
                <script type='text/javascript'>
                    $(document).ready(function() {

                        // Product List :: Search typeahead
                        var hints = [".$finalSearchList."];
                        $('#productFilter').typeahead({
                            source: hints,
                            updater: function(q) {
                                
                                // Progress
                                $('#divProgressBar').animate({width: '100%'}, 200);
                                $('#divProgress').removeClass('hidden');

                                // View Filter Selection
                                var btnThumbView = $('#btnThumbView');
                                var view = 'grid';
                                if (btnThumbView.hasClass('active')) {
                                    view = 'thumb';
                                }

                                // Active Filter Selection
                                var btnActiveView = $('#btnActiveView');
                                var activeFilter = '3';
                                if (btnActiveView.hasClass('active')) {
                                    activeFilter = '1';
                                }
                                var btnInactiveView = $('#btnInactiveView');
                                if (btnInactiveView.hasClass('active')) {
                                    if (activeFilter !== '3') {
                                        activeFilter = '2';
                                    } else {
                                        activeFilter = '0';
                                    }
                                }

                                // Filter
                                var filter = q;

                                // Category Filter
                                var catID = $('#categoryListResults').val();
                                if (catID = 'Loading...') {
                                    catID = null;
                                }

                                console.debug('[product filterResults] catID: ' + catID + ' filter: ' + filter + ' active: ' + activeFilter + ' view: ' + view);
                                // AJAX
                                var go = $.ajax({
                                    type: 'POST',
                                    data: {m: 'ajax',
                                        p: 'product',
                                        a: 'productList',
                                        view: view,
                                        filter: filter,
                                        active: activeFilter,
                                        categoryID: catID}
                                })
                                .done(function(results) {
                                    $('#divProductListResults').html(results);
                                    $('#divProgress').addClass('hidden');
                                    $('#divProgressBar').css('width','0%');
                                })
                                .fail(function(msg) {
                                    alert('Error:' + msg);
                                })
                                .always(function() {
                                });

                            }
                        });

                    });
                </script>";
            echo $pageJavaScript;
        }
        
        // View
        if ($_REQUEST['view'] == 'thumb') {
            
            // Thumb view
            include(cartPrivateDir . 'templates/' . $_SESSION['cartTheme'] . '/admin/product/product_list_view_thumb.inc.phtml');
            
        } elseif ($_REQUEST['view'] == 'grid') {
            
            // Grid view
            include(cartPrivateDir . 'templates/' . $_SESSION['cartTheme'] . '/admin/product/product_list_view_grid.inc.phtml');

        }
        
    }
    
    //
    // Product Dynamic Form/List Population
    //

    // Selected Cart -> Populate Category List
    if (isset($_REQUEST['a'])
            && $_REQUEST['a'] == 'category') {
        
        if (!empty($_REQUEST['productCartID'])) {
            $productCategory = new killerCart\Product_Category();
            $pcs             = $productCategory->getCategoryIDs('ALL', $_REQUEST['productCartID'], 'pc.categoryName');
            if (!empty($pcs)) {
                $out = '';
                foreach ($pcs as $pc) {
                    // Load info
                    $pcInfo = $productCategory->getCategoryInfo($pc['categoryID']);

                    // Only process parent levels
                    if (!empty($pcInfo['parentCategoryID'])) {
                        continue;
                    }
                    
                    // Label inactive
                    if (empty($pcInfo['categoryActive'])) {
                        $active = ' (Inactive)';
                    } else {
                        $active = '';
                    }
                            
                    $out .= "<option value='" . $pcInfo['categoryID'] . "'>";

                    // Children?
                    $children = $productCategory->getCategoryChildren($pcInfo['categoryID'], NULL, 'categoryName');
                    if (count($children) != 0) {
                        $out .= '[' . $pcInfo['categoryName'] . ']' . $active . '</option>';
                        foreach ($children as $child) {
                            $child = $productCategory->getCategoryInfo($child['categoryID']);
                            // Label inactive
                            if (empty($child['categoryActive'])) {
                                $active = ' (Inactive)';
                            } else {
                                $active = '';
                            }
                            $out .= "<option value='" . $child['categoryID'] . "'>&nbsp;-&nbsp;";

                            // Grand-Children?
                            $grandChildren = $productCategory->getCategoryChildren($child['categoryID'], NULL, 'pc.categoryName');
                            if (count($grandChildren) != 0) {
                                $out .= '[' . $child['categoryName'] . ']' . $active . '</option>';
                                foreach ($grandChildren as $grandChild) {
                                    $grandChild = $productCategory->getCategoryInfo($grandChild['categoryID']);
                                    // Label inactive
                                    if (empty($grandChild['categoryActive'])) {
                                        $active = ' (Inactive)';
                                    } else {
                                        $active = '';
                                    }
                                    $out .= "<option value='" . $grandChild['categoryID'] . "'>&nbsp;--&nbsp;";

                                    // Great-Grand-Children?
                                    $greatGrandChildren = $productCategory->getCategoryChildren($grandChild['categoryID'], NULL, 'pc.categoryName');
                                    if (count($greatGrandChildren) != 0) {
                                        $out .= '[' . $grandChild['categoryName'] . ']' . $active . '</option>';
                                        foreach ($greatGrandChildren as $greatGrandChild) {
                                            $greatGrandChild = $productCategory->getCategoryInfo($greatGrandChild['categoryID']);
                                            // Label inactive
                                            if (empty($greatGrandChild['categoryActive'])) {
                                                $active = ' (Inactive)';
                                            } else {
                                                $active = '';
                                            }
                                            $out .= "<option value='" . $greatGrandChild['categoryID'] . "'>&nbsp;---&nbsp";

                                            // Great-Great-Grand-Children?
                                            $greatGreatGrandChildren = $productCategory->getCategoryChildren($greatGrandChild['categoryID'], NULL, 'pc.categoryName');
                                            if (count($greatGreatGrandChildren) != 0) {
                                                $out .= '[' . $greatGrandChild['categoryName'] . ']' . $active . '</option>';
                                                foreach ($greatGreatGrandChildren as $greatGreatGrandChild) {
                                                    $greatGreatGrandChild = $productCategory->getCategoryInfo($greatGreatGrandChild['categoryID']);
                                                    // Label inactive
                                                    if (empty($greatGreatGrandChild['categoryActive'])) {
                                                        $active = ' (Inactive)';
                                                    } else {
                                                        $active = '';
                                                    }
                                                    $out .= "<option value='" . $greatGreatGrandChild['categoryID'] . "'>&nbsp;----&nbsp;" . $greatGreatGrandChild['categoryName'] . "</option>";
                                                }
                                            } else {
                                                $out .= $greatGrandChild['categoryName'] . $active . '</option>';
                                            }
                                        }
                                    } else {
                                        $out .= $grandChild['categoryName'] . $active . '</option>';
                                    }
                                }
                            } else {
                                $out .= $child['categoryName'] . $active . '</option>';
                            }
                        }
                    } else {
                        $out .= $pcInfo['categoryName'] . $active . '</option>';
                    }
                }
                echo "<select id='productCategoryID' name='productCategoryID'>
						<option disabled selected>--Select--</option>
                        " . $out . "
                      </select>";
            } else {
                ?>
                <div class="alert alert-info span4">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <strong>Sorry,</strong> no categories yet for cart <strong>#<?php echo $_REQUEST['productCartID']; ?></strong>.
                </div>
                <?php
            }
        }
    }

    // Selected Cart -> Populate Parent Product List
    if (isset($_REQUEST['a'])
            && $_REQUEST['a'] == 'product') {
        if (!empty($_REQUEST['productCartID'])) {
            $product = new killerCart\Product();
            $ps      = $product->getProductIDs('ALL', $_REQUEST['productCartID']);
            if (!empty($ps)) {
                echo "<select id='productParentID' name='productParentID'>
						<option disabled selected>--OPTIONAL--</option>
						<option value=''>(None)</option>";
                foreach ($ps as $p) {
                    $tp = $product->getProductInfo($p['productID']);
                    ?>
                    <option value='<?php echo $tp['productID']; ?>'>(<?php echo $tp['productID']; ?>) <?php echo $tp['productName']; ?></option>
                    <?php
                }
                echo "</select>";
            } else {
                echo "<select disabled><option>-- Select --</option></select>";
            }
        }
    }

    ////////////////////
    // Product Options
    ////////////////////

    //
    // Product Option Type Selected -> Populate next choice
    // and parent option list if applies
    if (isset($_REQUEST['a'])
            && $_REQUEST['a'] == 'type') {
        if (empty($_REQUEST['productID'])) {
            trigger_error('Missing product ID', E_USER_ERROR);
            return false;
        }
        $productID = $_REQUEST['productID'];
        $optionType = $_REQUEST['optionType'];

        // Default text input
        $input = "<input type='text' id='optionValueNew' name='optionValueNew[]' placeholder='Enter Option Value' />";

        switch ($optionType) {
            case "image": {
                    $msg   = 'You must upload your images before they will be listed below as options. An image option can be used to
                        allow the customer to visually choose between different option choices such as logos or designs after selecting
                        the main product.';
                    break;
                }

            case 'text': {
                    $msg = 'Text fields allow the customer to input text and is typically used for single words or short sentences.';
                    break;
                }

            case 'textarea': {
                    $msg   = 'Textarea fields allow the customer to input text and is typically used for multiple words, lists, or long sentences.';
                    $input = "<textarea id='optionValueNew' name='optionValueNew[]' rows='3' placeholder='Leave Blank or Enter Default Value'></textarea>";
                    break;
                }

            case 'select': {
                    $msg = 'Select options show a dropdown list of options to the customer to choose from. Only one option may be selected in a select option.';
                    break;
                }

            case 'radio': {
                    $msg = 'Radio options show a list of options to the customer to choose from. Only one option may be selected in a radio option.';
                    break;
                }

            case 'checkbox': {
                    $msg = 'Checkbox options show a list of options to the customer to choose from. Multiple options may be selected in a checkbox option.';
                    break;
                }

            default: {
                    break;
                }
        }
        ?>

        <!-- Note -->
        <div class='alert alert-info'>
            <a class="close" data-dismiss="alert" href="#">&times;</a>
            <h6><i class='icon-info-sign'></i>&nbsp;Note:</h6>
            <small><?php echo $msg; ?></small>
        </div>

        <?php
        // Get products options for parent list
        $product = new killerCart\Product();
        $pInfo = $product->getProductInfo($productID);
        \Errors::debugLogger(__FILE__.'@'.__LINE__);
        $productOptions = $product->getProductOptionsCustom($pInfo['cartID'], $productID);
        $start = "
        <!-- Parent select -->
        <div class='control-group'>
            <label class='control-label' for='optionParentNew'>Option Parent</label>
            <div class='controls'>
                <select id='optionParentNew' name='optionParentNew'>
                    <option value='0'>--OPTIONAL--</option>";
        foreach ($productOptions as $po) {
            $start .= "<option value='".$po['optionIDCustom']."'>#".$po['optionIDCustom']." ".$po['optionNameCustom']."</option>";
        }
        $start .= "
                </select>
            </div>
        </div>

        <!-- Has Parent -->
        <div id='divHasParent' class='hidden'>

            <!-- Hide by default? -->
            <div class='control-group'>
                <label class='control-label' for='optionHideByDefault'>Hide by default</label>
                <div class='controls'>
                <input type='checkbox' id='optionHideByDefault' name='optionHideByDefault' value='1' checked />
                </div>
            </div>

            <!-- Trigger to show select -->
            <div id='divTrigger' class='control-group'>
                <label class='control-label' for='optionParentTriggers'>Triggers</label>
                <div class='controls' id='divTriggerResults'></div>
            </div>
        </div>

        <!-- Option Label -->
        <div class='control-group'>
            <label class='control-label' for='optionNameNew'><strong>*Option Label</strong></label>
            <div class='controls'>
                <input type='text' id='optionNameNew' name='optionNameNew' class='validateRequired' placeholder='Option Label' />
            </div>
        </div>
        
        <!-- Option Required -->
        <div class='control-group'>
            <label class='control-label' for='optionRequiredNew'>Required</label>
            <div class='controls'>
                <span class='badge tooltip-on' data-toggle='tooltip' title='Option is not required to add to cart'>
                    <input type='radio' name='optionRequiredNew' value='0' class='validateRequired' checked /> No
                </span>
                <span class='badge tooltip-on' data-toggle='tooltip' title='Option is required to add to cart'>
                    <input type='radio' name='optionRequiredNew' value='1' class='validateRequired' /> Yes
                </span>
            </div>
        </div>";
        
        if ($optionType == 'image') {
            $start .= "
                <!-- Image Behavior -->
                <div class='control-group'>
                    <label class='control-label' for='optionBehaviorNew'><strong>*Behavior</strong></label>
                    <div class='controls'>
                        <span class='badge tooltip-on' data-toggle='tooltip' title='Single selection allowed (Radio)'>
                            <input type='radio' name='optionBehaviorNew' value='radio' class='validateRequired' /> Single
                        </span>
                        <span class='badge tooltip-on' data-toggle='tooltip' title='Multiple selections allowed (Checkbox)'>
                            <input type='radio' name='optionBehaviorNew' value='checkbox' class='validateRequired' /> Multiple
                        </span>
                    </div>
                </div>
                ";
        }
        
        $start .= "
        <script type='text/javascript'>
            // Admin :: Product :: Edit Product :: Add Option :: Has Parent? show/hide
            $('#optionParentNew').change(function() {
                if ($(this).val() != 0) {
                    $('#divHasParent').removeClass('hidden');

                    // Admin :: Product :: Edit Product :: Add Option :: Selected parent option, Populate Choices
                    var GO = $.ajax({
                        data: {m: 'ajax',
                            p: 'product',
                            a: 'getOptionChoices',
                            optionID: $(this).val()}
                    })
                    .done(function(results) {
                        $('#divTriggerResults').html(results);
                    })
                    .fail(function(msg) {
                        alert('Error:' + msg);
                    })
                    .always(function() {
                    });

                } else {
                    $('#divHasParent').addClass('hidden');
                }
            });

            // Admin :: Product :: Edit Product :: Add Option :: Has Parent :: Hide by default show/hide
            $('#optionHideByDefault').change(function() {
                if (this.checked) {
                    $('#divTrigger').removeClass('hidden');
                } else {
                    $('#divTrigger').addClass('hidden');
                }
            });
        </script>

        <!-- Input -->
        <table id='tblOptionInput'class='table table-condensed table-bordered'>
            <tr>
                <th>Option Value</th>
                <th>Option Price</th>
            </tr>";

        // Display Option
        if ($optionType != 'image') {
            echo $start . "
                <!-- Repeatable via jQuery -->
                <tr id='option_input1' class='clnProductOption'>
                    <td>" . $input . "</td>
                    <td>
                        <div class='input-prepend'>
                            <span class='add-on'>$</span>
                            <input type='text' class='span1 validateNumber' id='optionPriceNew' name='optionPriceNew[]' class='validateNumber' placeholder='Price' />
                        </div>
                    </td>
                </tr>
            </table>

            <!-- Add/Remove Buttons -->
            <div class='control-group'>
                <div class='controls'>
                    <button type='button' id='btnClnProductOption_Add' class='btn btn-mini'>
                        <i class='icon-plus-sign'></i>&nbsp;
                        Add Field
                    </button>
                    <button type='button' id='btnClnProductOption_Del' class='btn btn-mini'>
                        <i class='icon-remove-sign'></i>&nbsp;
                        Remove Field
                    </button>
                </div>
            </div>

            <!-- jQuery -->
            <script type='text/javascript'>
                // Admin :: Product :: Options :: Dynamically add/remove extra option rows
                $('#btnClnProductOption_Add').click(function() {
                    var num = $('.clnProductOption').length;
                    var newNum = new Number(num + 1);
                    var newElem = $('#option_input' + num).clone().attr('id', 'option_input' + newNum);
                    $('#option_input' + num).after(newElem);
                    $('#tblOptionInput tr:last').find('input[type=text]').val('');
                    $('#btnClnProductOption_Del').removeAttr('disabled');
                });
                $('#btnClnProductOption_Del').click(function() {
                    var num = $('.clnProductOption').length;
                    $('#option_input' + num).remove();
                    $('#btnClnProductOption_Add').removeAttr('disabled');
                    if (num - 1 == 1) {
                        $('#btnClnProductOption_Del').attr('disabled', 'disabled');
                    }
                });
                $('#btnClnProductOption_Del').attr('disabled', 'disabled');
            </script>";

        } else {
            //
            // Image option type specific
            //
            $image = new killerCart\Image();
            // Info
            $p = $product->getProductInfo($productID);
            // Max image display dimensions
            $maxImgW = '128';
            $maxImgH = '128';
            // Load images
            $p['images'] = $image->getImageParentInfo($p['cartID'], 1, $p['productID'], NULL, NULL, NULL, 'ALL');
            if (empty($p['images'])) {
                $showImgs = FALSE;
            } else {
                $i = 0;
                $showImgs = array();
                foreach ($p['images'] as $k => $img) {
                    $i++;
                    // Get best fit for this img
                    $bestFit = $image->getBestFitImage($p['cartID'], $img['imageID'], $img['imageWidth'], $img['imageHeight'], $maxImgW, $maxImgH);
                    $showImgs[] = array(
                        'imageID' => $img['imageID'],
                        'origWidth' => $img['imageWidth'],
                        'origHeight' => $img['imageHeight'],
                        'origSrc' => cartPublicUrl . "getfile.php?cartID=" . $p['cartID'] . "&imageID=" . $img['imageID'] . "&w=".$img['imageWidth']."&h=".$img['imageHeight'],
                        'bestFitSrc' => cartPublicUrl . "getfile.php?cartID=" . $p['cartID'] . "&imageID=" . $img['imageID'] . "&w=".$bestFit['bestWidth']."&h=".$bestFit['bestHeight'],
                        'showWidth' => $bestFit['showWidth']);
                }
            }
            if ($showImgs !== FALSE) {
                echo $start;
                $i = 0;
                foreach ($showImgs as $img) {
                    $i++;
                    echo "
                        <tr>
                            <td>
                                <table class='table table-condensed table-noborder'>
                                    <tr>
                                        <td>
                                            <input type='hidden' name='imageIDcounter[]' value='".$img['imageID']."' />
                                            <input type='checkbox' name='imageIDsNew[]' value='".$img['imageID']."' />
                                        </td>
                                        <td>
                                            <a href='" . $img['origSrc'] . "' target='_blank'><img class='img-polaroid' data-src='holder.js/".$maxImgW."x".$maxImgH."' src='" . $img['bestFitSrc'] . "' width='".$img['showWidth']."' /></a>
                                        </td>
                                        <td>
                                            <input type='text' name='imageLabelsNew[]' placeholder='Enter Option Value e.g. Side A' />
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td>
                                <div class='input-prepend'>
                                    <span class='add-on'>$</span>
                                    <input type='text' class='span1 validateNumber' name='imagePricesNew[]' class='validateNumber' placeholder='Price' />
                                </div>
                            </td>
                        </tr>";
                }
                echo "</table>";
            }
        }
    }
    
    //
    // Get Option Choices (Custom)
    //
    if (isset($_REQUEST['a'])
            && $_REQUEST['a'] == 'getOptionChoices') {
        $product = new killerCart\Product();
        $choices = $product->getProductOptionsChoicesCustom($_REQUEST['optionID']);
        $ccols = 3;
        if (count($choices) < $ccols) {
            $ccols = count($choices);
        }
        $maxSpan = 3;
        $magicCol = floor($maxSpan/$ccols);
        $out = "<div class='row'>
                    <div class='span".$maxSpan."'>
                        <div class='row'>";
        for ($ci=0; $ci<count($choices); $ci++) {
            // New Row
            if ($ci % $ccols == 0
                    && $ci != 0) {
                $out .= "
                    </div>
                    <div class='row'>";
            }
            // New Col
            $out .= "
                <div class='span".$magicCol."'>
                    <input type='checkbox' name='optionParentTriggers[]' value='".$choices[$ci]['choiceIDCustom']."' />&nbsp;".$choices[$ci]['choiceValueCustom']."
                </div>";
        }
        $out .= "
                    </div>
                </div>
            </div>";
        echo $out;
    }

    //
    // Get Options (for clone list)
    //
    if (isset($_REQUEST['a'])
            && $_REQUEST['a'] == 'getOptionsList') {
        $product = new killerCart\Product();
        echo json_encode($product->getProductOptions($_SESSION['cartID']));
    }

    //
    // Clone Cart Option ID - to Product Option ID for Customizing
    //
    if (isset($_REQUEST['a'])
            && $_REQUEST['a'] == 'cloneOptionID') {
        $product = new killerCart\Product();
        $cartID = $_SESSION['cartID'];
        $productID = $_REQUEST['productID'];
        $optionID = $_REQUEST['optionIDtoClone'];
        $optionInfo = $product->getProductOptions($cartID, $optionID);
        $optionType = $optionInfo['optionType'];
        $optionName = $optionInfo['optionName'];
        $optionBehavior = $optionInfo['optionBehavior'];
        $optionRequired = $optionInfo['optionRequired'];
        $start = "
            <input type='hidden' id='optionTypeNew' name='optionTypeNew' value='".$optionType."' />
            <input type='hidden' id='optionIsClone' name='optionIsClone' value='".$optionID."' />
            ";
        switch ($optionType) {
            case "image": {
                    $msg   = 'You must upload your images before they will be listed below as options. An image option can be used to
                        allow the customer to visually choose between different option choices such as logos or designs after selecting
                        the main product.';
                    break;
                }

            case 'text': {
                    $msg = 'Text fields allow the customer to input text and is typically used for single words or short sentences.';
                    break;
                }

            case 'textarea': {
                    $msg   = 'Textarea fields allow the customer to input text and is typically used for multiple words, lists, or long sentences.';
                    break;
                }

            case 'select': {
                    $msg = 'Select options show a dropdown list of options to the customer to choose from. Only one option may be selected in a select option.';
                    break;
                }

            case 'radio': {
                    $msg = 'Radio options show a list of options to the customer to choose from. Only one option may be selected in a radio option.';
                    break;
                }

            case 'checkbox': {
                    $msg = 'Checkbox options show a list of options to the customer to choose from. Multiple options may be selected in a checkbox option.';
                    break;
                }

            default: {
                    break;
                }
        }
        ?>

        <!-- Note -->
        <div class='alert alert-info'>
            <a class="close" data-dismiss="alert" href="#">&times;</a>
            <h6><i class='icon-info-sign'></i>&nbsp;Note:</h6>
            <small><?php echo $msg; ?></small>
        </div>

        <?php
        // Get products options for parent list
        #$pInfo = $product->getProductInfo($productID);
        $productOptions = $product->getProductOptionsCustom($cartID, $productID);
        $start .= "
        <!-- Parent select -->
        <div class='control-group'>
            <label class='control-label' for='optionParentNew'>Option Parent</label>
            <div class='controls'>
                <select id='optionParentNew' name='optionParentNew'>
                    <option value='0'>--OPTIONAL--</option>";
        foreach ($productOptions as $po) {
            $start .= "<option value='".$po['optionIDCustom']."'>#".$po['optionIDCustom']." ".$po['optionNameCustom']."</option>";
        }
        $start .= "
                </select>
            </div>
        </div>

        <!-- Has Parent -->
        <div id='divHasParent' class='hidden'>

            <!-- Hide by default? -->
            <div class='control-group'>
                <label class='control-label' for='optionHideByDefault'>Hide by default</label>
                <div class='controls'>
                <input type='checkbox' id='optionHideByDefault' name='optionHideByDefault' value='1' checked />
                </div>
            </div>

            <!-- Trigger to show select -->
            <div id='divTrigger' class='control-group'>
                <label class='control-label' for='optionParentTriggers'>Triggers</label>
                <div class='controls' id='divTriggerResults'></div>
            </div>
        </div>

        <!-- Option Label -->
        <div class='control-group'>
            <label class='control-label' for='optionNameNew'><strong>*Option Label</strong></label>
            <div class='controls'>
                <input type='text' id='optionNameNew' name='optionNameNew' class='validateRequired' placeholder='Option Label' value='".$optionName."' />
            </div>
        </div>";
        
        // Required
        if (!empty($optionRequired)) {
            $requiredYes = ' checked';
            $requiredNo = '';
        } else {
            $requiredYes = '';
            $requiredNo = ' checked';
        }
        $start .= "        
        <!-- Option Required -->
        <div class='control-group'>
            <label class='control-label' for='optionRequiredNew'>Required</label>
            <div class='controls'>
                <span class='badge tooltip-on' data-toggle='tooltip' title='Option is not required to add to cart'>
                    <input type='radio' name='optionRequiredNew' value='0' class='validateRequired'".$requiredNo." /> No
                </span>
                <span class='badge tooltip-on' data-toggle='tooltip' title='Option is required to add to cart'>
                    <input type='radio' name='optionRequiredNew' value='1' class='validateRequired'".$requiredYes." /> Yes
                </span>
            </div>
        </div>";
        
        if ($optionType == 'image') {
            // Behavior
            if ($optionBehavior == 'radio') {
                $radioYes = ' checked';
                $checkboxYes = '';
            } else {
                $radioYes = '';
                $checkboxYes = ' checked';
            }
            $start .= "
                <!-- Image Behavior -->
                <div class='control-group'>
                    <label class='control-label' for='optionBehaviorNew'><strong>*Behavior</strong></label>
                    <div class='controls'>
                        <span class='badge tooltip-on' data-toggle='tooltip' title='Single selection allowed (Radio)'>
                            <input type='radio' name='optionBehaviorNew' value='radio' class='validateRequired'".$radioYes." /> Single
                        </span>
                        <span class='badge tooltip-on' data-toggle='tooltip' title='Multiple selections allowed (Checkbox)'>
                            <input type='radio' name='optionBehaviorNew' value='checkbox' class='validateRequired'".$checkboxYes." /> Multiple
                        </span>
                    </div>
                </div>
                ";
        }
        
        $start .= "
        <script type='text/javascript'>
            // Admin :: Product :: Edit Product :: Add Option :: Has Parent? show/hide
            $('#optionParentNew').change(function() {
                if ($(this).val() != 0) {
                    $('#divHasParent').removeClass('hidden');

                    // Admin :: Product :: Edit Product :: Add Option :: Selected parent option, Populate Choices
                    var GO = $.ajax({
                        data: {m: 'ajax',
                            p: 'product',
                            a: 'getOptionChoices',
                            optionID: $(this).val()}
                    })
                    .done(function(results) {
                        $('#divTriggerResults').html(results);
                    })
                    .fail(function(msg) {
                        alert('Error:' + msg);
                    })
                    .always(function() {
                    });

                } else {
                    $('#divHasParent').addClass('hidden');
                }
            });

            // Admin :: Product :: Edit Product :: Add Option :: Has Parent :: Hide by default show/hide
            $('#optionHideByDefault').change(function() {
                if (this.checked) {
                    $('#divTrigger').removeClass('hidden');
                } else {
                    $('#divTrigger').addClass('hidden');
                }
            });
        </script>

        <!-- Input -->
        <table id='tblOptionInput'class='table table-condensed table-bordered'>
            <tr>
                <th>Option Value</th>
                <th>Option Price</th>
            </tr>";

        //
        // Display Option + Choices
        //
        if ($optionType != 'image') {
            // Echo
            echo $start;
            // Choices
            $clonedChoices = $product->getProductOptionsChoices($optionID);
            $cci = 0;
            $end = '';
            #var_dump($clonedChoices);
            foreach ($clonedChoices as $clonedChoice) {
                $cci++;
                $choiceValue = $clonedChoice['choiceValue'];
                $choicePrice = $clonedChoice['choicePrice'];
                $choiceSortOrder = $clonedChoice['choiceSortOrder'];
                if ($cci === 1) {
                    $end .= "<!-- Repeatable via jQuery -->";
                     $class = " class='clnProductOption'";
                }
                $end .= "
                <tr id='option_input".$cci."'".$class.">
                    <td>";
                // textarea or ?
                if ($optionType == 'textarea') {
                    $end .= "<textarea id='optionValueNew' name='optionValueNew[]' rows='4'>".killerCart\Util::convertBRToNL($choiceValue)."</textarea>";
                } else {
                    $end .= "<input type='text' name='optionValueNew[]' value='" . $choiceValue . "' />";
                }
                $end .= "
                    </td>
                    <td>
                        <div class='input-prepend'>
                            <!-- Price -->
                            <span class='add-on'>$</span>
                            <input type='text' class='span1 validateNumber' id='optionPriceNew' name='optionPriceNew[]' class='validateNumber' placeholder='Price' value='".$choicePrice."' />
                        </div>
                    </td>
                </tr>";                    
            }

            $end .= "
            </table>

            <!-- Add/Remove Buttons -->
            <div class='control-group'>
                <div class='controls'>
                    <button type='button' id='btnClnProductOption_Add' class='btn btn-mini'>
                        <i class='icon-plus-sign'></i>&nbsp;
                        Add Field
                    </button>
                    <button type='button' id='btnClnProductOption_Del' class='btn btn-mini'>
                        <i class='icon-remove-sign'></i>&nbsp;
                        Remove Field
                    </button>
                </div>
            </div>

            <!-- jQuery -->
            <script type='text/javascript'>
                // Admin :: Product :: Options :: Dynamically add/remove extra option rows
                $('#btnClnProductOption_Add').click(function() {
                    var num = $('.clnProductOption').length;
                    var newNum = new Number(num + 1);
                    var newElem = $('#option_input' + num).clone().attr('id', 'option_input' + newNum);
                    $('#option_input' + num).after(newElem);
                    $('#tblOptionInput tr:last').find('input[type=text]').val('');
                    $('#btnClnProductOption_Del').removeAttr('disabled');
                });
                $('#btnClnProductOption_Del').click(function() {
                    var num = $('.clnProductOption').length;
                    $('#option_input' + num).remove();
                    $('#btnClnProductOption_Add').removeAttr('disabled');
                    if (num - 1 == 1) {
                        $('#btnClnProductOption_Del').attr('disabled', 'disabled');
                    }
                });
                $('#btnClnProductOption_Del').attr('disabled', 'disabled');
            </script>";
            echo $end;

        } else {
            //
            // Image option type specific
            //
            $image = new killerCart\Image();
            // Info
            $p = $product->getProductInfo($productID);
            // Max image display dimensions
            $maxImgW = '128';
            $maxImgH = '128';
            // Load images
            $p['images'] = $image->getImageParentInfo($p['cartID'], 1, $p['productID'], NULL, NULL, NULL, 'ALL');
            if (empty($p['images'])) {
                $showImgs = FALSE;
            } else {
                $i = 0;
                $showImgs = array();
                foreach ($p['images'] as $k => $img) {
                    $i++;
                    // Get best fit for this img
                    $bestFit = $image->getBestFitImage($p['cartID'], $img['imageID'], $img['imageWidth'], $img['imageHeight'], $maxImgW, $maxImgH);
                    $showImgs[] = array(
                        'imageID' => $img['imageID'],
                        'origWidth' => $img['imageWidth'],
                        'origHeight' => $img['imageHeight'],
                        'origSrc' => cartPublicUrl . "getfile.php?cartID=" . $p['cartID'] . "&imageID=" . $img['imageID'] . "&w=".$img['imageWidth']."&h=".$img['imageHeight'],
                        'bestFitSrc' => cartPublicUrl . "getfile.php?cartID=" . $p['cartID'] . "&imageID=" . $img['imageID'] . "&w=".$bestFit['bestWidth']."&h=".$bestFit['bestHeight'],
                        'showWidth' => $bestFit['showWidth']);
                }
            }
            if ($showImgs !== FALSE) {
                echo $start;
                $i = 0;
                foreach ($showImgs as $img) {
                    $i++;
                    echo "
                        <tr>
                            <td>
                                <table class='table table-condensed table-noborder'>
                                    <tr>
                                        <td>
                                            <input type='hidden' name='imageIDcounter[]' value='".$img['imageID']."' />
                                            <input type='checkbox' name='imageIDsNew[]' value='".$img['imageID']."' />
                                        </td>
                                        <td>
                                            <a href='" . $img['origSrc'] . "' target='_blank'><img class='img-polaroid' data-src='holder.js/".$maxImgW."x".$maxImgH."' src='" . $img['bestFitSrc'] . "' width='".$img['showWidth']."' /></a>
                                        </td>
                                        <td>
                                            <input type='text' name='imageLabelsNew[]' placeholder='Enter Option Value e.g. Side A' />
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td>
                                <div class='input-prepend'>
                                    <span class='add-on'>$</span>
                                    <input type='text' class='span1 validateNumber' name='imagePricesNew[]' placeholder='Price' />
                                </div>
                            </td>
                        </tr>";
                }
                echo "</table>";
            }
        }
    }
    
    //
    // Get Option Configure Form (for modal)
    //
    if (isset($_REQUEST['a'])
            && $_REQUEST['a'] == 'getOptionModifyForm') {
        $product = new killerCart\Product();
        $optionID = $_REQUEST['optionID'];
        $optionInfo = $product->getProductOptionsCustom($_REQUEST['cartID'], $_REQUEST['productID'], $optionID);
        $optionName = $optionInfo['optionNameCustom'];
        $optionType = $optionInfo['optionType'];
        $optionBehavior = $optionInfo['optionBehaviorCustom'];
        $optionRequired = $optionInfo['optionRequiredCustom'];
        if ($optionRequired == '1') {
            $yesRequired = ' checked';
            $noRequired = '';
        } else {
            $noRequired = ' checked';
            $yesRequired = '';
        }
        // Required
        $out = "
            <table class='table table-bordered'>
                <tr>
                    <th colspan='2'>".$optionName."</th>
                </tr>
                <tr>
                    <td>Required</td>
                    <td>
                        <input type='radio' name='configureOptionRequiredNew' value='0'".$noRequired." /> No
                        <input type='radio' name='configureOptionRequiredNew' value='1'".$yesRequired." /> Yes
                    </td>
                </tr>";
        // Behavior
        if (!empty($optionBehavior)) {
            if ($optionBehavior == 'checkbox') {
                $checkbox = ' checked';
                $radio = '';
            } else {
                $radio = ' checked';
                $checkbox = '';
            }
            $out .= "
                <tr>
                    <td>Behavior</td>
                    <td>
                        <input type='radio' name='configureOptionBehaviorNew' value='checkbox'".$checkbox." /> Multiple (Checkbox)<br />
                        <input type='radio' name='configureOptionBehaviorNew' value='radio'".$radio." /> Single (Radio)
                    </td>
                </tr>";
        }
        // Parent Option Triggers (if level > 1)
        $parentOptionTriggers = $product->getOptionParentCustom($optionID);
        if (!empty($parentOptionTriggers)) {
            $out .= "
                    <tr>
                        <td>Triggers</td>
                        <td>
                            <input type='hidden' name='option".$optionID."TriggerChanged' value='1' />
                        ";
            $parentOptionIDCustom = $parentOptionTriggers[0]['parentOptionIDCustom'];
            // All Parent Option Choice IDs possible as triggers:
            $allChoices = $product->getProductOptionsChoicesCustom($parentOptionIDCustom);
            foreach ($allChoices as $choice) {
                // Trigger Choice IDs to pre-select
                foreach ($parentOptionTriggers as $triggerChoiceID) {
                    if ($choice['choiceIDCustom'] == $triggerChoiceID['triggerChoiceIDCustom']) {
                        $selected = ' checked';
                        break;
                    } else {
                        $selected = '';
                    }
                }
                $out .= "<input type='checkbox' name='option".$optionID."ParentTriggers[]' value='".$choice['choiceIDCustom']."'".$selected." /> ".$choice['choiceValueCustom']."<br />";
            }
            $out .= "</td></tr>";
        }
        echo $out;
    }

    //
    // Product Option (Remove)
    //
    if (isset($_REQUEST['a'])
            && $_REQUEST['a'] == 'removeProductOption') {
        $product = new killerCart\Product();
        // Success
        if ($product->removeProductOptionCustom($_REQUEST['productID'], $_REQUEST['optionID'])) {
            ?>
            <div class='alert alert-block alert-success span6 offset3'>
                <button type='button' class='close' data-dismiss='alert'>&times;</button>
                <h4><i class="icon-ok"></i>&nbsp;Success!</h4>
                Product Option removed successfully. Click <a href='?p=product'>here</a> to return to Products.
            </div>
            <?php
            // Failure
        } else {
            ?>
            <div class='alert alert-block alert-error span6 offset3'>
                <button type='button' class='close' data-dismiss='alert'>&times;</button>
                <h4><i class="icon-warning-sign"></i>&nbsp;Error!</h4>
                Unable to remove Product Option. Details have been logged and emailed to the administrator. Click <a href='?p=product'>here</a> to return to Products.
            </div>
            <?php
        }
    }

    //
    // Product Option Choice (Remove)
    //
    if (isset($_REQUEST['a'])
            && $_REQUEST['a'] == 'removeProductOptionChoiceCustom') {
        $product = new killerCart\Product();
        if (preg_match('/^(option)(\d+)(choice)(\d+)/', $_REQUEST['choiceID'], $matches)) {
            $optionID = $matches[2];
            $choiceID = $matches[4];
        }
        // Success
        if ($product->removeProductOptionChoiceCustom($optionID, $choiceID)) {
            ?>
            <div class='alert alert-block alert-success span6 offset3'>
                <button type='button' class='close' data-dismiss='alert'>&times;</button>
                <h4>Success!</h4>
                Product Option Choice removed successfully. Click <a href='?p=product'>here</a> to return to Products.
            </div>
            <?php
            // Failure
        } else {
            ?>
            <div class='alert alert-block alert-error span6 offset3'>
                <button type='button' class='close' data-dismiss='alert'>&times;</button>
                <h4>Error!</h4>
                Unable to remove Product Option Choice. Details have been logged and emailed to the administrator. Click <a href='?p=product'>here</a> to return to Products.
            </div>
            <?php
        }
    }

    //
    // Product Option Choice (Add)
    //
    if (isset($_REQUEST['a'])
            && $_REQUEST['a'] == 'addProductOptionChoiceCustom') {
        $cartID    = $_REQUEST['cartID'];
        $productID  = $_REQUEST['productID'];
        $optionIDCustom   = $_REQUEST['optionIDCustom'];
        $product    = new killerCart\Product();
        $option     = $product->getProductOptionsCustom($cartID, $productID, $optionIDCustom);
        $optionType = $option['optionType'];
        $input = "<input type='text' name='option" . $optionIDCustom . "NewValue[]' placeholder='Enter Option Value' />";
        switch ($optionType) {
            case "image": {
                    $image = new killerCart\Image();
                    $images = $image->getImageParentInfo($cartID, NULL, $productID);
                    // Image dropdown selection usin ddslick
                    $input .= "

                        <select class='newOption".$optionIDCustom."ChoiceImg'>";
                    foreach ($images as $img) {
                        $imgUrl = cartPublicUrl."getfile.php?cartID=".$img['cartID']."&imageID=".$img['imageID']."&w=".$img['imageWidth']."&h=".$img['imageHeight'];
                        $input .= "<option value='".$img['imageID']."' data-imagesrc='".$imgUrl."'></option>";
                    }
                    $input .= "</select>";
                    // JS ddslick
                    $input .= "
                        <script>
                            $('.newOption".$optionIDCustom."ChoiceImg').ddslick({
                                imagePosition:'right',
                                width:'128',
                                onSelected: function(data) {
                                    //console.debug('onSelected');
                                    var thisImgID = data.selectedData.value;
                                    var thisOptionID = ".$optionIDCustom.";
                                    //console.debug('thisImgID: ' + thisImgID + ' thisOptionID: ' + thisOptionID);

                                    // Remove pre-existing hidden fields we populated
                                    $('.dynamicHiddenChoiceImg').each(function() {
                                        //console.debug('Removing previous');
                                        //console.debug($(this).val());
                                        $(this).remove();
                                    });
                                    // Each selected item add hidden fields with updated values
                                    $('.dd-selected-value').each(function() {
                                        //console.debug('dd-selected-value found:' + $(this).val());
                                        $('<input>').attr({
                                            type: 'hidden',
                                            class: 'dynamicHiddenChoiceImg',
                                            name: 'option' + thisOptionID + 'NewChoiceImg[]',
                                            value: $(this).val()
                                        }).appendTo($('#tblOptionID".$optionIDCustom."'));
                                    });
                                }
                            });
                        </script>";
                    break;
                }

            case 'textarea': {
                    $input = "<textarea name='option" . $optionIDCustom . "NewValue[]' rows='3' placeholder='Leave Blank or Enter Default Value'></textarea>";
                    break;
                }

            default: {
                    break;
                }
        }
        $newRow = "
                <tr>
                    <td colspan='2'>" . $input . "</td>
                    <td>
                        <div class='input-prepend'>
                            <span class='add-on'>$</span>
                            <input type='text' class='span1 validateNumber' name='option" . $optionIDCustom . "NewPrice[]' placeholder='Price' />
                        </div>
                    </td>
                </tr>";
        echo $newRow;
    }
    
    //
    // Product Option :: Move Up/Down
    //
    if (isset($_REQUEST['a'])
            && $_REQUEST['a'] == 'moveOption') {
        $productID = $_REQUEST['productID'];
        $optionIDCustom = $_REQUEST['optionIDCustom'];
        $oldOrder = $_REQUEST['oldOrder'];
        $newOrder = $_REQUEST['newOrder'];
        $product = new killerCart\Product();
        $product->updateProductOptionSortOrderCustom($productID, $optionIDCustom, $oldOrder, $newOrder);
        echo 'Success!';
    }

    //
    // Product Option Choice :: Move Up/Down
    //
    if (isset($_REQUEST['a'])
            && $_REQUEST['a'] == 'moveChoice') {
        $optionIDCustom = $_REQUEST['optionIDCustom'];
        $choiceIDCustom = $_REQUEST['choiceIDCustom'];
        $oldOrder = $_REQUEST['oldOrder'];
        $newOrder = $_REQUEST['newOrder'];
        $product = new killerCart\Product();
        $product->updateProductOptionChoiceSortOrderCustom($optionIDCustom, $choiceIDCustom, $oldOrder, $newOrder);
        echo 'Success!';
    }

    ////////////////////
    // Product Images //
    ////////////////////

    //
    // Product Image (Toggle Visibility)
    //
    if (isset($_REQUEST['a'])
            && $_REQUEST['a'] == 'setImageVisibility') {
        // Init Image
        $image      = new killerCart\Image();

        $showInCarousel = null;
        if (isset($_POST['showInCarousel']))
        {
            $showInCarousel = $_POST['showInCarousel'];
        }
        
        $showInCarouselThumbs = null;
        if (isset($_POST['showInCarouselThumbs']))
        {
            $showInCarouselThumbs = $_POST['showInCarouselThumbs'];
        }
        
        // Update visibility
        $image->setImageVisibility($_POST['cartID'], $_POST['imageID'], $showInCarousel, $showInCarouselThumbs);        
    }

    //
    // Product Image (Remove)
    //
    if (isset($_REQUEST['a'])
            && $_REQUEST['a'] == 'remove_product_image') {
        // Init Image
        $image      = new killerCart\Image();
        $image->setImageActive($_POST['cartID'], $_POST['imageID'], 0);
    }

    //
    // Product Image :: Move Up/Down
    //
    if (isset($_REQUEST['a'])
            && $_REQUEST['a'] == 'moveImg') {
        $productID = $_REQUEST['productID'];
        $imageID = $_REQUEST['imageID'];
        $oldOrder = $_REQUEST['oldOrder'];
        $newOrder = $_REQUEST['newOrder'];
        $image = new killerCart\Image();
        $image->updateProductImageSortOrder($productID, $imageID, $oldOrder, $newOrder);
        echo 'Success!';
    }

    ///////////////////////////
    // Save Product (step 2) //
    ///////////////////////////
    if (isset($_REQUEST['a'])
            && $_REQUEST['a'] == 'edit_product') {

        $product = new killerCart\Product();

        //
        // ACL Check (when trying to edit x id)
        //
        if (!empty($_REQUEST['productID'])) {
            $pInfo = $product->getProductInfo($_REQUEST['productID']);
            if ($pInfo['cartID'] != $_SESSION['cartID'] && $_SESSION['groupID'] != 1) {
                trigger_error('Security alert.', E_USER_ERROR);
                return false;
            }
        }

        //
        // Save Product
        //
        if (!empty($_REQUEST['s'])
                && $_REQUEST['s'] == 2) {

            // Validate & Sanitize _POST
            $args = array(
                'productID'                 => FILTER_SANITIZE_NUMBER_INT,
                'productCategoryID'         => FILTER_SANITIZE_NUMBER_INT,
                'productParentID'           => FILTER_SANITIZE_NUMBER_INT,
                'productCartID'            => FILTER_SANITIZE_NUMBER_INT,
                'productStatus'             => FILTER_SANITIZE_NUMBER_INT,
                'productName'               => FILTER_SANITIZE_SPECIAL_CHARS,
                'productSKU'                => FILTER_SANITIZE_SPECIAL_CHARS,
                'productPrice'              => array('filter' => FILTER_SANITIZE_NUMBER_FLOAT,
                    'flags'  => FILTER_FLAG_ALLOW_FRACTION),
                'productDescriptionPublic'  => FILTER_UNSAFE_RAW,
                'productDescriptionPrivate' => FILTER_UNSAFE_RAW,
                'productSpecifications' => FILTER_UNSAFE_RAW,
                'productShipping' => FILTER_UNSAFE_RAW,
                'productWarranty' => FILTER_UNSAFE_RAW,
                'productTaxable'            => FILTER_SANITIZE_NUMBER_INT,
                'productPrivacy'            => FILTER_SANITIZE_NUMBER_INT,
                'productEmail'              => FILTER_SANITIZE_EMAIL);

            // Sanitize all (existing) product options
            foreach ($_POST as $k => $v) {
                
                if (preg_match('/^(option)(\d+)(Name)/', $k, $matches)
                        || preg_match('/^(option)(\d+)(Type)/', $k, $matches)
                        || preg_match('/^(option)(\d+)(detail)(\d+)(Value)/', $k, $matches)
                        || preg_match('/^(option)(\d+)(Behavior)/', $k, $matches)
                        || preg_match('/^(option)(\d+)(Required)/', $k, $matches)
                        || preg_match('/^(option)(\d+)(SortOrder)/', $k, $matches)
                        || preg_match('/^(option)(\d+)(TriggerChanged)/', $k, $matches)
                ) {
                    $args[$matches[0]] = FILTER_UNSAFE_RAW;
                    
                } elseif (preg_match('/^(option)(\d+)(detail)(\d+)(Price)/', $k, $matches)) {
                    
                    $args[$matches[0]] = array('filter' => FILTER_SANITIZE_NUMBER_FLOAT,
                        'flags'  => FILTER_FLAG_ALLOW_FRACTION);
                    
                } elseif (preg_match('/^(option)(\d+)(detail)(\d+)(ImageID)/', $k, $matches)) {
                    
                    $args[$matches[0]] = FILTER_SANITIZE_NUMBER_INT;
                    
                }
            }
            // Validate & Sanitize to continue
            $s   = new killerCart\Sanitize;
            if (!$san = $s->filterArray(INPUT_POST, $args)) {
                $validated = FALSE;
            } else {
                $validated = TRUE;
            }

            // Defaults
            if ($san['productStatus'] == '') {
                $san['productStatus'] = 1; // Could set to 0 here by default to prevent WIP products from being on live storefront until ready
            }
            $defaults = array('productID' => 'DEFAULT',
                'productParentID' => 'NULL',
                'productEmail' => 'NULL',
                'productSKU' => 'NULL');
            foreach ($defaults as $k=>$def) {
                if (empty($san[$k])) {
                    $san[$k] = $def;
                }
            }
            // Options (new)
            foreach ($_POST as $k => $v) {
                if (isset($san[$k])
                        || ($v == ''
                                && empty($_POST['optionParentNew']))) {
                    continue;
                }
                if (preg_match('/^(option)(\d+)(NewValue)/', $k, $matches)
                        || preg_match('/^(option)(\d+)(NewPrice)/', $k, $matches)
                        || preg_match('/^(option)(\d+)(NewChoiceImg)/', $k, $matches)
                        || preg_match('/^(optionTypeNew)/', $k, $matches)
                        || preg_match('/^(optionNameNew)/', $k, $matches)
                        || preg_match('/^(optionValueNew)/', $k, $matches)
                        || preg_match('/^(optionPriceNew)/', $k, $matches)
                        || preg_match('/^(optionParentNew)/', $k, $matches)
                        || preg_match('/^(optionHideByDefault)/', $k, $matches)
                        || preg_match('/^(optionParentTriggers)/', $k, $matches)
                        || preg_match('/^(optionBehaviorNew)/', $k, $matches)
                        || preg_match('/^(optionRequiredNew)/', $k, $matches)
                        #|| preg_match('/^(optionSortOrderNew)/', $k, $matches)
                        || preg_match('/^(imageIDcounter)/', $k, $matches)
                        || preg_match('/^(imageIDsNew)/', $k, $matches)
                        || preg_match('/^(imageLabelsNew)/', $k, $matches)
                        || preg_match('/^(imagePricesNew)/', $k, $matches)
                ) {
                    $san[$k] = $v;
                }
            }

            $productPrice = killerCart\Util::getFormattedNumber($san['productPrice']);
            if ($validated === TRUE) {
                
                // Update existing product
                if ($san['productID'] != 'DEFAULT') {

                    // Save all product options (only applies to existing products)
                    foreach ($san as $k => $v) {

                        //
                        // Save Options
                        //
                        //
                        // Save Existing Option
                        //
                        if (preg_match('/^(option)(\d+)(Name)/', $k, $matches)) {
                            $optionIDCustom = $matches[2];
                            $name     = $san['option' . $optionIDCustom . 'Name'];
                            $type     = $san['option' . $optionIDCustom . 'Type'];
                            $required     = $san['option' . $optionIDCustom . 'Required'];
                            $optionSortOrder = $san['option' . $optionIDCustom . 'SortOrder'];
                            if (!empty($san['option'.$optionIDCustom.'TriggerChanged'])) {
                                $updateTriggers = $san['option'.$optionIDCustom.'TriggerChanged'];
                            } else {
                                $updateTriggers = FALSE;
                            }

                            //
                            // Parent Option / Triggers?
                            //
                            if (!empty($_POST['option'.$optionIDCustom.'Parent'])) {
                                $parentOptionID = $_POST['option'.$optionIDCustom.'Parent'];
                                
                                // Modified triggers, save new
                                if (!empty($updateTriggers)) {
                                    if (!empty($_POST['option'.$optionIDCustom.'ParentTriggers'])) {
                                        $triggerChoiceIDs = $_POST['option'.$optionIDCustom.'ParentTriggers'];
                                    } else {
                                        $triggerChoiceIDs = NULL;
                                    }
                                    // Clear existing Triggers
                                    $product->clearProductOptionChoiceTriggersCustom($parentOptionID, $optionIDCustom);
                                    // Save new Triggers
                                    // Save Parent / Child / but no trigger association (shown by default)
                                    if (empty($triggerChoiceIDs)) {
                                        $product->saveProductOptionChoiceTriggerCustom($parentOptionID, $optionIDCustom);
                                    } else {
                                        // Save Parent / Child / with Trigger associations (hidden by default)
                                        foreach ($triggerChoiceIDs as $triggerChoiceID) {
                                            $product->saveProductOptionChoiceTriggerCustom($parentOptionID, $optionIDCustom, $triggerChoiceID);
                                        }
                                    }
                                }
                            }

                            // Image Type Behavior
                            if ($type == 'image'
                                    && !empty($san['option' . $optionIDCustom . 'Behavior'])
                                    ) {
                                $behavior = $san['option' . $optionIDCustom . 'Behavior'];
                            } else {
                                $behavior = NULL;
                            }
                            $globalOption = $product->getProductOptionsCustom($san['productCartID'], $san['productID'], $optionIDCustom);
                            $optionIDGlobal = $globalOption['optionIDGlobal'];
                            if (($optionIDCustom = $product->saveProductOptionCustom($optionIDCustom, $optionIDGlobal, $san['productID'], $name,
                                    $behavior, $required, $optionSortOrder)) === FALSE) {
                                $validated = false;
                                break;
                            }
                            if ($optionIDCustom == 0) {
                                $optionIDCustom = $matches[2];
                            }
                            $matched = 0;
                            
                            //
                            // Save Option Choices
                            //
                            
                            // @todo parent/child updated?
                            // @todo change trigger
                            foreach ($san as $k2 => $v2) {
                                // Existing Values/Labels
                                if (preg_match('/^(option)(' . $optionIDCustom . ')(detail)(\d+)(Value)/', $k2, $matches)) {
                                    $choiceIDCustom = $matches[4];
                                    $value    = killerCart\Util::convertNLToBR($v2);
                                    $matched++;
                                    // Existing Prices
                                } elseif (preg_match('/^(option)(' . $optionIDCustom . ')(detail)(\d+)(Price)/', $k2, $matches)) {
                                    $choiceIDCustom = $matches[4];
                                    $price    = $v2;
                                    $matched++;
                                    // NEW Choices
                                } elseif (preg_match('/^(option)(' . $optionIDCustom . ')(NewValue)/', $k2, $matches)) {
                                    foreach ($v2 as $nID => $newValue) {
                                        if ($newValue == '') {
                                            //continue; // Commented = Allows adding of blank choice fields for text fields
                                        }
                                        //
                                        // Save New Choice (Custom)
                                        //
                                        $choiceIDCustom = 'DEFAULT';
                                        //$optionIDCustom = $optionIDCustom;
                                        $choiceIDGlobal = NULL; // We dont want to make a global choice, only a custom local choice
                                        $choiceValueCustom = killerCart\Util::convertNLToBR($newValue);
                                        $choicePriceCustom = $_POST['option' . $optionIDCustom . 'NewPrice'][$nID];
                                        // Choice Image
                                        if (!empty($san['option'.$optionIDCustom.'NewChoiceImg'])) {
                                            $choiceImageIDCustom = $san['option'.$optionIDCustom.'NewChoiceImg'][$nID];
                                        } else {
                                            $choiceImageIDCustom = NULL; // @todo
                                        }
                                        $choiceSortOrder = NULL; // New choice = max sort = taken care of in class
                                        $choiceActiveCustom = NULL;
                                        if ($product->saveProductOptionChoicesCustom($choiceIDCustom, $optionIDCustom, $choiceIDGlobal,
            $choiceValueCustom, $choicePriceCustom, $choiceImageIDCustom, $choiceSortOrder, $choiceActiveCustom) === FALSE) {
                                            $validated = false;
                                            break;
                                        }
                                    }
                                }
                                // Existing: If dont have a Value/Price pair keep collecting (New Choices are added in 1 step)
                                if ($matched != 2) {
                                    continue;
                                }

                                // Has image ID?
                                if ($type == 'image') {
                                    $imageID = $san['option'.$optionIDCustom.'detail'.$choiceIDCustom.'ImageID'];
                                } else {
                                    $imageID = NULL;
                                }

                                //
                                // Save Existing Choice (Custom)
                                //
                                //$choiceIDCustom = $choiceIDCustom;
                                //$optionIDCustom = $optionIDCustom;
                                $origChoiceInfo = $product->getProductOptionsChoicesCustom($optionIDCustom, $choiceIDCustom);
                                $choiceIDGlobal = $origChoiceInfo['choiceIDGlobal']; // No change
                                $choiceValueCustom = $value;
                                $choicePriceCustom = $price;
                                $choiceImageIDCustom = $imageID;
                                $choiceSortOrder = 100; // @todo
                                $choiceActiveCustom = NULL;
                                if ($product->saveProductOptionChoicesCustom($choiceIDCustom, $optionIDCustom, $choiceIDGlobal,
    $choiceValueCustom, $choicePriceCustom, $choiceImageIDCustom, $choiceSortOrder, $choiceActiveCustom) === FALSE) {
                                    $validated = false;
                                    break;
                                }
                                $matched = 0;
                            }

                        } elseif (preg_match('/^(optionNameNew)/', $k, $matches)) {
                            //
                            // Save New Option
                            //

                            // Option Info
                            $name     = $san['optionNameNew'];
                            $type     = $san['optionTypeNew'];
                            $required     = $san['optionRequiredNew'];
                            $active = NULL; // NULL for active, 0 for inactive

                            // Image Type Behavior
                            if ($type == 'image') {
                                $behavior = $san['optionBehaviorNew'];
                            } else {
                                $behavior = NULL;
                            }

                            //
                            // Create New Cart Option
                            //
                            if (empty($_POST['optionIsClone'])) {
                                $optionID = $product->saveProductOptionCart('DEFAULT', $_SESSION['cartID'], $name, $type, $behavior, $required);
                                if ($optionID === FALSE) {
                                    $validated = false;
                                    break;
                                }
                            } else {
                                // If clone, dont create new global option but instead link custom option to orig clone global option ID
                                $optionID = $_POST['optionIsClone'];
                            }

                            //
                            // Clone Cart Option to Custom Product Option
                            //
                            $optionIDCustom = $product->saveProductOptionCustom('DEFAULT', $optionID, $san['productID'], $name, $behavior, $required, NULL);
                            if ($optionIDCustom === FALSE) {
                                $validated = false;
                                break;
                            }

                            if ($type != 'image') {
                                //
                                // Create Option Choices (non-image)
                                //
                                foreach ($san as $k2 => $v2) {
                                    if (preg_match('/^(optionValueNew)/', $k2, $matches)) {
                                        foreach ($v2 as $nID => $newValue) {
                                            if (empty($_POST['optionIsClone'])) { // If not a clone
                                                // Create New Cart Choice
                                                $choiceIDGlobal = $product->saveProductOptionChoicesCart('DEFAULT', $optionID,
                                                        killerCart\Util::convertNLToBR($newValue), $_POST['optionPriceNew'][$nID]);
                                                if ($choiceIDGlobal === FALSE) {
                                                    $validated = false;
                                                    break;
                                                }
                                            } else {
                                                $choiceIDGlobal = NULL;
                                            }
                                            // Custom Choice Sort Order @todo
                                            $choiceSortOrder = 1;
                                            // Clone Cart Choice to Custom Product Option Choice
                                            $choiceIDCustom = $product->saveProductOptionChoicesCustom('DEFAULT', $optionIDCustom,
                                                    $choiceIDGlobal, killerCart\Util::convertNLToBR($newValue), $_POST['optionPriceNew'][$nID], NULL, $choiceSortOrder);
                                            if ($choiceIDCustom === FALSE) {
                                                $validated = false;
                                                break;
                                            }
                                        }
                                    }
                                }
                            } else {
                                //
                                // Create Option Choices (Images)
                                //
                                foreach ($san['imageIDsNew'] as $x => $y) {
                                    $imageID = $y;
                                    $key = array_search($imageID, $san['imageIDcounter']);
                                    $optLabel = $san['imageLabelsNew'][$key];
                                    $optPrice = $san['imagePricesNew'][$key];
                                    if (empty($_POST['optionIsClone'])) { // If not a clone
                                        // Create New Cart Choice
                                        $choiceIDGlobal = $product->saveProductOptionChoicesCart('DEFAULT', $optionID,
                                                $optLabel, $optPrice);
                                        if ($choiceIDGlobal === FALSE) {
                                            $validated = false;
                                            break;
                                        }
                                    } else {
                                        $choiceIDGlobal = NULL;
                                    }
                                    // Custom Choice Sort Order @todo
                                    $choiceSortOrder = 1;
                                    // Clone Cart Choice to Custom Product Option Choice
                                    $choiceIDCustom = $product->saveProductOptionChoicesCustom('DEFAULT', $optionIDCustom,
                                            $choiceIDGlobal, $optLabel, $optPrice, $imageID, $choiceSortOrder);
                                    if ($choiceIDCustom === FALSE) {
                                        $validated = false;
                                        break;
                                    }
                                }
                            }

                            //
                            // Parent Option / Triggers?
                            //
                            $parentOptionID = $san['optionParentNew'];
                            if (!empty($san['optionParentTriggers'])) {
                                $triggerChoiceIDs = $san['optionParentTriggers'];
                            } else {
                                $triggerChoiceIDs = FALSE;
                            }
                            // Save Parent / Child / but no trigger association
                            if (!empty($parentOptionID)
                                    && empty($triggerChoiceIDs)) {
                                #$product->saveChoiceTrigger($parentOptionID, $optionID);
                                $product->saveProductOptionChoiceTriggerCustom($parentOptionID, $optionIDCustom);
                            } elseif (!empty($parentOptionID)
                                    && !empty($triggerChoiceIDs)) {
                                // Save Parent / Child / Trigger associations
                                foreach ($triggerChoiceIDs as $triggerChoiceID) {
                                    #$product->saveChoiceTrigger($parentOptionID, $optionID, $trigger);
                                    $product->saveProductOptionChoiceTriggerCustom($parentOptionID, $optionIDCustom, $triggerChoiceID);
                                }
                            }
                            
                        }
                    }
                }
            }
            
            //
            // Save Uploaded Images
            //
            if ($validated === TRUE) {
                // Images (only applies to existing products)
                if ($san['productID'] != 'DEFAULT') {
                    // Has images to upload/save
                    if (!empty($_FILES)) {
                        if (file_exists($_FILES['files']['tmp_name'][0])
                                && is_uploaded_file($_FILES['files']['tmp_name'][0])) {
                            // Upload image
                            killerCart\Util::handleFileUpload('files', 'image', $san['productCartID'], $san['productID']);
                        }
                    }
                }
            }
            
            //
            // Save Product
            // 
            // Success
            if ($validated === TRUE && $product->saveProduct($san['productID'], $san['productCategoryID'], $san['productParentID'],
                                                             $san['productCartID'], $san['productStatus'], $san['productName'],
                                                             $productPrice, $san['productDescriptionPublic'],
                                                             $san['productDescriptionPrivate'], $san['productTaxable'],
                                                             $san['productPrivacy'], $san['productEmail'], $san['productSKU'],
                                                             $san['productSpecifications'], $san['productShipping'],
                                                             $san['productWarranty'])) {
                if ($san['productID'] == 'DEFAULT') {
                    $productID = $product->id;
                } else {
                    $productID = $san['productID'];
                }
                ?>
                <div class='alert alert-block alert-success'>
                    <button type='button' class='close' data-dismiss='alert'>&times;</button>
                    <h4>Success!</h4>
                    Product saved successfully.
                    <ul>
                        <li><a href='?p=product&a=edit_product&productID=<?php echo $productID; ?>'>View this product</a></li>
                    </ul>
                </div>
                <?php
                // Failure
            } else {
                ?>
                <div class='alert alert-block alert-error'>
                    <button type='button' class='close' data-dismiss='alert'>&times;</button>
                    <h4>Error!</h4>
                    Unable to save Product. Details have been logged and emailed to the administrator. Click <a href='?p=product'>here</a> to return to Products.
                </div>
                <?php
            }
        }
    }

    exit;
}
//
// END: Ajax call 
//
?>

<?php
//
// Product Form Submitted
//
if (isset($_REQUEST['a'])) {

    //
    // Action: edit_product 	
    //
    if ($_REQUEST['a'] == 'edit_product') {

        // Init Product Category and Product
        $category = new killerCart\Product_Category();
        $product = new killerCart\Product();
        if (!empty($_REQUEST['productID'])) {
            $pInfo = $product->getProductInfo($_REQUEST['productID']);
            //
            // ACL Check: Extra security check
            //
            if ($pInfo['cartID'] != $_SESSION['cartID']
                    && $_SESSION['groupID'] != 1
                    ) {
                trigger_error('Security alert.', E_USER_ERROR);
                return false;
            }
        }

        // Step 1: Show Edit Product Form
        if (empty($_REQUEST['s'])) {

            include(cartPrivateDir . 'templates/' . $_SESSION['cartTheme'] . '/admin/product/product_edit.inc.phtml');

        }
    }

} else {
    //
    // Product Header
    //
    $product                  = new killerCart\Product();
    $productCount['Active']   = $product->getProductCount('Active', $cartToGet);
    $productCount['Inactive'] = $product->getProductCount('Inactive', $cartToGet);
    $productCount['All']      = $productCount['Active'] + $productCount['Inactive'];
    include(cartPrivateDir . 'templates/' . $_SESSION['cartTheme'] . '/admin/product/product_head.inc.phtml');
    
    // No form submitted: List Products 
    include(cartPrivateDir . 'templates/' . $_SESSION['cartTheme'] . '/admin/product/product_list.inc.phtml');

}

// New Product Form 
include(cartPrivateDir . 'templates/' . $_SESSION['cartTheme'] . '/admin/product/product_new_form.inc.phtml');