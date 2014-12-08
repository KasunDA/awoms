<?php
Errors::debugLogger("API_include...",10);
/**
 * For use with jQueryUI:
 */

/* ACL: No Create or Update or Delete access so No Form */
if (!ACL::IsUserAuthorized($this->controller, "create")
        && !ACL::IsUserAuthorized($this->controller, "update")
        && !ACL::IsUserAuthorized($this->controller, "delete"))
{
    Errors::debugLogger("API_INCLUDE: ACL empty, RETURN...", 1, TRUE);
    return false;
}

/* Main API File */
$deleteButtonClass = "delete";
$isModal = "true";
$autoOpenForm = "true";
if ($this->action == "readall")
{
    $loadFile = 'create';
    $loadAction = 'create';
    $titlePrefix = "Add ";
    $autoOpenForm = "false";
    $deleteButtonClass = "hidden";
}
elseif ($this->action == "create")
{
    $loadFile = 'create';
    $loadAction = 'create';
    $titlePrefix = "Add ";
    $deleteButtonClass = "hidden";
}
elseif ($this->action == "update")
{
    $loadFile = 'create';
    $loadAction = 'update';
    $titlePrefix = "Update ";
}
elseif ($this->action == "delete")
{
    $loadFile = 'create';
    $loadAction = 'delete';
    $titlePrefix = "Delete ";
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
    "\"@@".$loadFile."AutoOpenForm@@\"",
    "@@deleteButtonClass@@",
    "\"@@isModal@@\"");

// Title
$controller = preg_replace("/s$/","", strtolower($this->controller));
$titleLabel = ucfirst($controller);
if ($titleLabel == "Usergroup") {$titleLabel = "Group";}
$title = $titlePrefix.$titleLabel;
$saveLabel = "Save ".$titleLabel;
$tinyMCEInputID = isset($tinyMCEInputID) ? $tinyMCEInputID : NULL;

Errors::debugLogger(__FILE__."@".__LINE__." tinyMCEInputID: $tinyMCEInputID",10);

$replace = array($formID,
    $title,
    $this->controller,
    $loadAction, // desired action, not current action
    $saveLabel,
    $tinyMCEInputID,
    $autoOpenForm,
    $deleteButtonClass,
    $isModal);

$actionJS = file_get_contents(ROOT.DS."application".DS."views".DS."API".DS.$loadFile.".js");
$finalJS = str_replace($find,$replace,$actionJS);

if (!empty($pageJavaScript))
{
   array_unshift($pageJavaScript, $finalJS);
} else {
   $pageJavaScript[] = $finalJS;
}