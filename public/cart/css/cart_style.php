<?php

$privateDir = $_REQUEST['privateDir'];
$theme      = $_REQUEST['theme'];
$cssFile    = $privateDir . 'templates/' . $theme . '/' . $theme . '.css';
if (is_file($cssFile)) {
    header("Content-type: text/css; charset: UTF-8");
    header("Content-length: " . filesize($cssFile));
    readfile($cssFile);
}
?>
