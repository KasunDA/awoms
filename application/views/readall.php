<?php
// Items / Label
$label = preg_replace("/s$/", "", ucfirst($this->controller));
$lbl   = strtolower($label);
if ($label == "Usergroup")
{
    $label = "Group";
}
$c     = $this->controller;
$items = $$c;

// ACL: Create Button
$showCreateButton = FALSE;
if (ACL::IsUserAuthorized($this->controller, 'create'))
{
    $showCreateButton = TRUE;
}

// Show Brand?
$showBrandColumn = FALSE;
if (!empty($items) && $isGlobalAdmin && $this->controller != "brands")
{
    if ($this->controller == "users")
    {
        // Users dont have brandID but their Usergroup does:
        // Users -> Usergroups -> Brands
        // Brands List (single db call)
        $Brand           = new Brand();
        $brands          = $Brand->getWhere();
        $showBrandColumn = TRUE;
    }
    elseif ($this->controller == "carts")
    {
        // Carts dont have brandID but a Brand can:
        // Brands List (single db call)
        $Brand           = new Brand();
        $brands          = $Brand->getWhere();
        $showBrandColumn = TRUE;
    }
    else
    {
        // Only show brand column if this is an item that has Brand to show
        foreach ($items as $item)
        {
            if (!empty($item['brandID']))
            {
                $showBrandColumn = TRUE;
                // Brands List (single db call)
                $Brand           = new Brand();
                $brands          = $Brand->getWhere();
                break;
            }
        }
    }
}

// Show Store?
$showStoreColumn = FALSE;
if (!empty($items) && $isGlobalAdmin && $this->controller != "stores")
{
    if ($this->controller == "users")
    {
        // Users dont have storeID but their Usergroup does:
        // Users -> Usergroups -> Stores
        // Stores List (single db call)
        //(@TODO: if user owns multiple stores?)
//        $Store           = new Store();
//        $stores          = $Store->getWhere();
//        $showStoreColumn = TRUE;
    }
    elseif ($this->controller == "carts")
    {
        // Carts dont have storeID but a Store can:
        // Stores List (single db call)
        $Store           = new Store();
        $stores          = $Store->getWhere();
        $showStoreColumn = TRUE;
    }
    else
    {
        // Only show brand column if this is an item that has Brand to show
        foreach ($items as $item)
        {
            if (!empty($item['storeID']))
            {
                $showStoreColumn = TRUE;
                // Stores List (single db call)
                $Store           = new Store();
                $stores          = $Store->getWhere();
                break;
            }
        }
    }
}

// Show Usergroup?
$showUsergroupColumn = FALSE;
if (!empty($items)
    && $this->controller != "usergroups"
    && !empty($_SESSION['user'])
    && in_array($_SESSION['user']['usergroup']['usergroupName'], array('Administrators', 'Store Owners')))
{
    foreach ($items as $item)
    {
        if (!empty($item['usergroupID']))
        {
            $showUsergroupColumn = TRUE;
            // Usergroups List (single db call)
            $Usergroup           = new Usergroup();
            $usergroups          = $Usergroup->getWhere();
            break;
        }
    }
}
?>

<div class="main_readall">
    <div class='main_output'>
        <?php
        // Load custom template if exists
        $fileLocations = Utility::getTemplateFileLocations('readall_list');
        foreach ($fileLocations as $fileLoc)
        {
            include($fileLoc);
        }
        ?>
    </div>
</div>
