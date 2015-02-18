
<!-- Normalize, Reset(main), jQueryUI -->
<link rel="stylesheet" href="/css/normalize.min.css">
<link rel="stylesheet" href="/css/main.css">
<link rel="stylesheet" href="/css/menu.css">

<!-- Custom Brand CSS -->
<?php
if (file_exists(ROOT.DS.'public'.DS.'css'.DS.$_SESSION['brand']['brandLabel'].DS.$_SESSION['brand']['brandLabel'].".css"))
{
    $customCssUrl = BRAND_URL.'css/'.BRAND_LABEL.'/'.BRAND_LABEL.'.css';
    echo "<link rel='stylesheet' href='".$customCssUrl."'>";
}
?>

<!--
/**
 * For use with jQueryUI:
 */
<link rel="stylesheet" href="/css/admin/jquery-ui-1.10.4.custom.min.css">
<link rel='stylesheet' href='/css/admin/admin.css'>
<link rel="stylesheet" href="/css/admin/jquery-ui-1.10.4.custom.min.css">
<link rel="stylesheet" href="/css/GPFC/jquery-ui-1.10.4.custom.min.css">
-->
