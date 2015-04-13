<?php
// Create Button
if ($showCreateButton)
{
    ?>
    <button type='button' class="button_save" onClick="location.href = '/<?php echo $this->controller . "/create"; ?>';">
        Add <?php echo $label; ?>
    </button>
    <?php
}

// No Items?
if (empty($items))
{
    echo "<div class='alert warning'>Sorry! There are no " . $label . "s yet.</div>";
    return;
}

// View: Administrator|StoreOwner
if (!empty($_SESSION['user'])
    && in_array($_SESSION['user']['usergroup']['usergroupName'], array('Administrators', 'Store Owners')))
{
    $fileLocations = Utility::getTemplateFileLocations('readall_list_authenticated');
    foreach ($fileLocations as $fileLoc)
    {
        include($fileLoc);
    }
    return;
}

// View: Public (store locator)
$fileLocations = Utility::getTemplateFileLocations('readall_list_public');
foreach ($fileLocations as $fileLoc)
{
    include($fileLoc);
}
