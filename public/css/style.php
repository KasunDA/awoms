<?php
// @todo
$theme   = $_REQUEST['theme'];
$cssFile = ROOT . DS . 'templates/' . $theme . '/' . $theme . '.css';
if (is_file($cssFile)) {
  header("Content-type: text/css; charset: UTF-8");
  header("Content-length: " . filesize($cssFile));
  readfile($cssFile);
}
?>