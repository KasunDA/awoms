<?php
/* Main API File */
if ($this->action == "readall")
{
    $loadFile = 'create';
    $loadAction = 'create';
    $titlePrefix = "Add ";
    $autoOpenForm = "false";
}
elseif ($this->action == "create")
{
    $loadFile = 'create';
    $loadAction = 'create';
    $titlePrefix = "Add ";
    $autoOpenForm = "true";
}
elseif ($this->action == "update")
{
    $loadFile = 'create';
    $loadAction = 'update';
    $titlePrefix = "Update ";
    $autoOpenForm = "true";
}
elseif ($this->action == "delete")
{
    $loadFile = 'create';
    $loadAction = 'delete';
    $titlePrefix = "Delete ";
    $autoOpenForm = "true";
}
else
{
    return;
}

// JS
$find = array("@@".$loadFile."FrmID@@",
    "@@".$loadFile."Title@@",
    "@@".$loadFile."Controller@@",
    "@@".$loadFile."Action@@",
    "@@".$loadFile."SaveText@@",
    "@@".$loadFile."TinyMCEInputID@@",
    "\"@@".$loadFile."AutoOpenForm@@\"");

// Title
$controller = rtrim(strtolower($this->controller), "s");
$titleLabel = ucfirst($controller);
if ($titleLabel == "Usergroup") {
    $titleLabel = "Group";
}
$title = $titlePrefix.$titleLabel;
$saveLabel = "Save ".$titleLabel;
$tinyMCEInputID = isset($tinyMCEInputID) ? $tinyMCEInputID : NULL;
$replace = array($formID,
    $title,
    $this->controller,
    $loadAction, // desired action, not current action
    $saveLabel,
    $tinyMCEInputID,
    $autoOpenForm);

$actionJS = file_get_contents(ROOT.DS."application".DS."views".DS."API".DS.$loadFile.".js");
$finalJS = str_replace($find,$replace,$actionJS);

if (!empty($pageJavaScript))
{
   array_unshift($pageJavaScript, $finalJS);
} else {
   $pageJavaScript[] = $finalJS;
}