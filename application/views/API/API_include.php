<?php
if ($this->action == "viewall" || $this->action == "create")
{
    $loadFile = 'create';
    $loadAction = 'create';
    $titlePrefix = "Add ";
}
elseif ($this->action == "edit")
{
    $loadFile = 'create';
    $loadAction = 'edit';
    $titlePrefix = "Edit ";
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
    "@@".$loadFile."SaveText@@");

// Title
$controller = rtrim(strtolower($this->controller), "s");
$titleLabel = ucfirst($controller);
if ($titleLabel == "Usergroup") {
    $titleLabel = "Group";
}
$title = $titlePrefix.$titleLabel;
$saveLabel = "Save ".$titleLabel;
$replace = array($formID,
    $title,
    $this->controller,
    $loadAction, // desired action, not current action
    $saveLabel);

$actionJS = file_get_contents(ROOT.DS."application".DS."views".DS."API".DS.$loadFile.".js");
$finalJS = str_replace($find,$replace,$actionJS);
array_unshift($pageJavaScript, $finalJS);
