<?php

// Security check: Deny direct file access; must be loaded through index getfile
if (count(get_included_files()) == 1) {
    header("Location: index.php");
    die("403");
}
?>
<?php
// Init Product Category
$category      = new killerCart\Product_Category();
    
// If a category was chosen or there is only 1 category, go to that category
$cat_count = $category->getCategoryIDs('ACTIVE', $cartID, 'pc.categoryName');
if (!empty($c)
        || count($cat_count) == 1)
{
    if (empty($c))
    {
        // Get only category available
        $c = $category->getCategoryInfo($cat_count[0]['categoryID']);
    }
    include(cartPrivateDir . 'templates/' . $cart->session['cartTheme'] . '/product_category/product_category_view.inc.phtml');
}
else
{
    // Get all categories
    $categoryIDs   = $category->getCategoryIDs('ACTIVE', $cartID, 'pc.categoryName');
    
    include(cartPrivateDir . 'templates/' . $cart->session['cartTheme'] . '/product_category/product_category_list.inc.phtml');
}
?>