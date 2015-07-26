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
if (empty($_SESSION['user']['ACL']['cart']['read'])
        && empty($_SESSION['user']['ACL']['global']['read'])
) {
    trigger_error('Security alert.', E_USER_ERROR);
    return false;
}
//
// ACL Check: Cart limits
//
if ($_SESSION['user']['usergroup']['usergroupID'] == 1) { // Global Admin
    $cartToGet = NULL; // All
} else {
    $cartToGet = $_SESSION['cartID']; // This cart only
}
?>

<?php
//
// BEGIN: Ajax call 
//
if (isset($_REQUEST['m'])
        && ($_REQUEST['m'] == 'ajax')) {
    
    // Category List:
    if (isset($_REQUEST['a'])
            && $_REQUEST['a'] == 'categoryList') {
        
        // Init Product Category
        $category      = new killerCart\Product_Category();
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
        // Get all categories
        $orderBy = "(SELECT pc.categoryName FROM productCategories AS pc WHERE pc.categoryID = ref.parentCategoryID), pc.categoryName";
        if (empty($_REQUEST['filter'])) {
            $categoryIDs   = $category->getCategoryIDs($active, $cartToGet, $orderBy);
            $customWhere = '';
        } else {
            // Filtered
            $customWhere = "
                    (pc.categoryName LIKE '%".$_REQUEST['filter']."%' OR 
                    pc.categoryCode LIKE '%".$_REQUEST['filter']."%')";
            $categoryIDs   = $category->getCategoryIDs($active, $cartToGet, $orderBy, $customWhere);
            // @todo full text, array of words to MATCH AGAINST pc.categoryDescriptionPrivate LIKE '%".$_REQUEST['filter']."%' OR 
            // @todo full text, array of words to MATCH AGAINST pc.categoryDescriptionPublic LIKE '%".$_REQUEST['filter']."%'
        }
        
        if (empty($categoryIDs)) {
            // Actually no categories yet
            if (empty($_REQUEST['filter'])) {
                echo "
                    <div class='alert alert-block alert-info span3 offset4'>
                        <button type='button' class='close' data-dismiss='alert'>&times;</button>
                        <h4><i class='icon-info-sign'></i>&nbsp;Sorry!</h4>
                        Please add a category to begin
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
            $allCatNames = array();
            $allCatCodes = array();
            foreach ($categoryIDs as $cat) {
                $allCatNames[] = json_encode($cat['categoryName']); // Name
                $allCatCodes[] = json_encode($cat['categoryCode']); // Code
                #if (!empty($cat['categoryDescriptionPrivate'])) {
                #$allCatDescPriv[] = json_encode($cat['categoryDescriptionPrivate']); // Private Description
                #}
                #if (!empty($cat['categoryDescriptionPublic'])) {
                #$allCatDescPub[] = json_encode($cat['categoryDescriptionPublic']); // Public Description
                #}
            }
            $searchListCatNames = implode(", ", $allCatNames);
            $searchListCatCodes = implode(", ", $allCatCodes);
            #$searchListCatDescPriv = implode(", ", $allCatDescPriv);
            #$searchListCatDescPub  = implode(", ", $allCatDescPub);
            $finalSearchList    = $searchListCatNames . "," . $searchListCatCodes; #.",".$searchListCatDescPriv.",".$searchListCatDescPub;
            $pageJavaScript = "
                <script type='text/javascript'>
                    $(document).ready(function() {

                        // Product Category List :: Search typeahead
                        var hints = [".$finalSearchList."];
                        $('#categoryFilter').typeahead({
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

                                console.debug('[product_category filterResults] filter: ' + filter + ' active: ' + activeFilter + ' view: ' + view);
                                // AJAX
                                var go = $.ajax({
                                    type: 'POST',
                                    data: {m: 'ajax',
                                        p: 'product_category',
                                        a: 'categoryList',
                                        view: view,
                                        filter: filter,
                                        active: activeFilter}
                                })
                                .done(function(results) {
                                    $('#divCategoryListResults').html(results);
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
            include(cartPrivateDir . 'templates/' . $_SESSION['cartTheme'] . '/admin/product_category/product_category_list_view_thumb.inc.phtml');
            
        } elseif ($_REQUEST['view'] == 'grid') {
            
            // Grid view
            include(cartPrivateDir . 'templates/' . $_SESSION['cartTheme'] . '/admin/product_category/product_category_list_view_grid.inc.phtml');

        }

    }
    
    //
    // Product Category Dynamic Form/List Population
    //

    // Selected Cart -> Populate Category List
    if (isset($_REQUEST['a'])
            && $_REQUEST['a'] == 'getCategories') {
        $productCategory = new killerCart\Product_Category();
        $pcs             = $productCategory->getCategoryIDs('ALL', $_REQUEST['cartID'], 'pc.categoryName');
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
                $children = $productCategory->getCategoryChildren($pcInfo['categoryID'], NULL, 'pc.categoryName');
                if (count($children) != 0) {
                    $out .= '[' . $pcInfo['categoryName'] . ']'.$active.'</option>';
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
                            $out .= '[' . $child['categoryName'] . ']'.$active.'</option>';
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
                                    $out .= '[' . $grandChild['categoryName'] . ']'.$active.'</option>';
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
                                            $out .= '[' . $greatGrandChild['categoryName'] . ']'.$active.'</option>';
                                            foreach ($greatGreatGrandChildren as $greatGreatGrandChild) {
                                                $greatGreatGrandChild = $productCategory->getCategoryInfo($greatGreatGrandChild['categoryID']);
                                                // Label inactive
                                                if (empty($greatGreatGrandChild['categoryActive'])) {
                                                    $active = ' (Inactive)';
                                                } else {
                                                    $active = '';
                                                }
                                                $out .= "<option value='" . $greatGreatGrandChild['categoryID'] . "'>&nbsp;----&nbsp;" . $greatGreatGrandChild['categoryName'] . $active . "</option>";
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
            echo "<select id='category_parentID' name='category_parentID'>
                    <option value='' selected>--OPTIONAL--</option>
                    " . $out . "
                  </select>";
        } else {
            echo "<select disabled>
                    <option value='' disabled selected>--N/A--</option>
                  </select>";
        }
    }

    // Category Image (Remove)
    if (isset($_REQUEST['a'])
            && $_REQUEST['a'] == 'removeImage') {
        // Init Image
        $image      = new killerCart\Image();
        $image->setImageActive($_POST['cartID'], $_POST['imageID'], 0);
    }

    // Save Category (step 2)
    if (isset($_REQUEST['a'])
            && $_REQUEST['a'] == 'edit_category') {
        // Init Category
        $category = new killerCart\Product_Category();

        //
        // ACL Check (when trying to edit x id)
        //
        if (!empty($_REQUEST['categoryID'])) {
            $pcInfo = $category->getCategoryInfo($_REQUEST['categoryID']);
            if ($pcInfo['cartID'] != $_SESSION['cartID'] && $_SESSION['user']['usergroup']['usergroupID'] != 1) {
                trigger_error('Security alert.', E_USER_ERROR);
                return false;
            }
        }

        //
        // Save Category
        //
        if (!empty($_REQUEST['s']) && $_REQUEST['s'] == 2) {
            // Validate & Sanitize _POST
            $args = array(
                'categoryID'                   => FILTER_SANITIZE_NUMBER_INT,
                'category_cartID'             => FILTER_SANITIZE_NUMBER_INT,
                'category_status'              => FILTER_SANITIZE_NUMBER_INT,
                'category_parentID'            => FILTER_SANITIZE_NUMBER_INT,
                'level_number'                 => FILTER_SANITIZE_NUMBER_INT,
                'categoryName'                 => FILTER_SANITIZE_SPECIAL_CHARS,
                'category_code'                => FILTER_SANITIZE_SPECIAL_CHARS,
                'category_descriptionPublic'         => FILTER_UNSAFE_RAW,
                'category_descriptionPrivate' => FILTER_UNSAFE_RAW,
                'category_taxable'             => FILTER_SANITIZE_NUMBER_INT,
                'category_privacy'             => FILTER_SANITIZE_NUMBER_INT,
                'category_showPrice'           => FILTER_SANITIZE_NUMBER_INT
            );

            // Sanitize (POST only!)
            $s   = new killerCart\Sanitize();
            $san = $s->filterArray(INPUT_POST, $args);
            if (!$san) {
                $validated = FALSE;
            } else {
                $validated = TRUE;
            }

            // Product Category Defaults
            if (empty($san['categoryID'])) {
                $san['categoryID'] = 'DEFAULT';
            }
            if (!isset($san['category_status']) || $san['category_status'] == '') {
                $san['category_status'] = 1;
            }
            if (empty($san['category_code'])) {
                $san['category_code'] = strtoupper(substr(str_replace(' ', '', $san['categoryName']), 0, 7));
            }
            $catID = $category->saveCategory($san['category_cartID'], $san['categoryID'], $san['category_status'],
                                             $san['category_parentID'], $san['level_number'], $san['categoryName'],
                                             $san['category_code'], $san['category_descriptionPublic'],
                                             $san['category_descriptionPrivate'], $san['category_taxable'],
                                             $san['category_privacy'], $san['category_showPrice']);
            if ($validated === TRUE) {
                // Images (only applies to existing products)
                if ($san['categoryID'] != 'DEFAULT') {
                    if (!empty($_FILES)) {
                        if (file_exists($_FILES['files']['tmp_name'][0])
                                && is_uploaded_file($_FILES['files']['tmp_name'][0])) {
                            killerCart\Util::handleFileUpload('files', 'image', $san['category_cartID'], NULL, $san['categoryID']);
                        }
                    }
                }
            }
            
            // Success
            if ($validated === TRUE && isset($catID)) {
                if ($san['categoryID'] != 'DEFAULT') {
                    $catID = $san['categoryID'];
                }
                ?>
                <div class="alert alert-block alert-success">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <h4>Success!</h4>
                    Product Category saved successfully.<br />
                    <ul>
                        <li><a href='?p=product_category'>Return to Product Categories</a></li>
                        <li><a href='?p=product_category&a=edit_category&categoryID=<?php echo $catID; ?>'>Reload this product category</a></li>
                    </ul>
                </div>
                <?php
                // Failure
            } else {
                ?>
                <div class="alert alert-block alert-error">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <h4>Error!</h4>
                    Unable to save Product Category. Details have been logged and emailed to the administrator. Click <a href='?p=product_category'>here</a> to return to Product Categories.
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
// Category Form Submitted
//
if (isset($_REQUEST['a'])) {

    //
    // Action: edit_category
    //
    if ($_REQUEST['a'] == "edit_category") {

        $category = new killerCart\Product_Category();
        if (!empty($_REQUEST['categoryID'])) {
            $pcInfo          = $category->getCategoryInfo($_REQUEST['categoryID']);
            // ????? $pcInfo['image'] = $category->getCategoryImage($pcInfo['cartID'], $pcInfo['categoryID']);
            //
            // ACL Check: Extra security check
            //
            if ($pcInfo['cartID'] != $_SESSION['cartID']
                    && $_SESSION['user']['usergroup']['usergroupID'] != 1
                    ) {
                trigger_error('Security alert.', E_USER_ERROR);
                return false;
            }
        }

        // Step 1: Show Edit Category Form
        if (empty($_REQUEST['s'])) {

            include(cartPrivateDir . 'templates/' . $_SESSION['cartTheme'] . '/admin/product_category/product_category_edit.inc.phtml');

        }
    }

} else {
    //
    // Product Category Header
    //
    $category                  = new killerCart\Product_Category();
    $categoryCount['Active']   = $category->getCategoryCount('Active', $cartToGet);
    $categoryCount['Inactive'] = $category->getCategoryCount('Inactive', $cartToGet);
    $categoryCount['All']      = $categoryCount['Active'] + $categoryCount['Inactive'];
    include(cartPrivateDir . 'templates/' . $_SESSION['cartTheme'] . '/admin/product_category/product_category_head.inc.phtml');

    // No form submitted: List Categories
    include(cartPrivateDir . 'templates/' . $_SESSION['cartTheme'] . '/admin/product_category/product_category_list.inc.phtml');
}

// New Category Form
include(cartPrivateDir . 'templates/' . $_SESSION['cartTheme'] . '/admin/product_category/product_category_new_form.inc.phtml');