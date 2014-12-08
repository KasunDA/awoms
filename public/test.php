<?php
if (!defined("DEVELOPMENT_ENVIRONMENT") || !DEVELOPMENT_ENVIRONMENT) { die("Test"); }
phpinfo();
var_dump($_SESSION);
?>