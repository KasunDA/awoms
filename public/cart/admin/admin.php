<?php
// Security check: Deny direct file access; must be loaded through index getfile
if (count(get_included_files()) == 1) {
    header("Location: index.php");
    die("403");
}
?>
<?php
// Admin Home Template
include(cartPrivateDir . 'templates/' . $_SESSION['cartTheme'] . '/admin/admin_home.phtml');
?>