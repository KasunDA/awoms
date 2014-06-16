<form id='<?php echo $formID; ?>'  method='POST'>
    <input type='hidden' name='step' value='2' />
    <input type='hidden' name='inp_menuID' value='<?php echo $menuID; ?>' />

    <table cellpadding='2' cellspacing='0'>

<?php
    // Brand List - Non-Global-Admins (BrandID=1, Group=Admin) == limited by brand
    if (!empty($brandChoiceList))
    {
        $class='';
        if (empty($_SESSION['user'])
                || $_SESSION['user']['usergroup']['usergroupName'] != "Administrators"
                || $_SESSION['user']['usergroup']['brandID'] != 1)
        {
            $class='hidden';
        }
?>
    <tr class='<?php echo $class; ?>'>
      <td>
        <!-- Brand -->
        Brand
      </td>
      <td>
        <select name='inp_brandID'>
          <?=$brandChoiceList;?>
        </select>
      </td>
    </tr>
<?php
}
?>

        <tr>
            <td>
                Menu Name
            </td>
            <td>
                <input type='text' id='inp_menuName' name='inp_menuName' value='<?php
        if (isset($inp_menuName)) {
            echo $inp_menuName;
        } else {
            echo "Default Heading Navigation Menu";
        }
        ?>' size='40' autocomplete="off" />
            </td>
        </tr>
        
        <tr>
            <td>
                Active
            </td>
            <td>
                <select id='inp_menuActive' name='inp_menuActive'/>
                    <?php
                    if (!empty($inp_menuActive)) {
                        echo "<option value='1' selected>Active</option>";
                        echo "<option value='0'>Not Active</option>";
                    } else {
                        echo "<option value='1'>Active</option>";
                        echo "<option value='0' selected>Not Active</option>";
                    }
                    ?>
                </select>
            </td>
        </tr>

    </table>


<?php
$pageJavaScript[] = file_get_contents(ROOT.DS.'application'.DS.'views'.DS.'menus'.DS.'menuJavaScript.js');

$class = "hidden";
if ($menuID == 'DEFAULT')
{
    $class = "";
}
?>
    
    <!-- Menu Links -->

    <table cellpadding='2' cellspacing='0'>
        <tr>
            <th align="center" width="100">
                <button type="button" id="addLink" class="alert only-img add" title="Add Link"></button>
            </th>
            <th width="210">
                Display
            </th>
            <th width="210">
                Alias URL
            </th>
            <th width="210">
                Actual URL
            </th>
        </tr>
    </table>

    <table cellpadding='2' cellspacing='0' id="menuLinksTable">
        
        <!-- Cloneable row -->
        <tr class="<?php echo $class; ?>">
            <th align="center" width="100">
                <button type="button" class="alert only-img up" title="Move Up"></button>
                <button type="button" class="alert only-img down" title="Move Down"></button>
                <button type="button" class="alert only-img remove" title="Remove"></button>
            </td>
            <td width="200">
                <input type='text' name='inp_menuLinkDisplay[]' size='20' />
            </td>
            <td width="200">
                <input type='text' name='inp_menuLinkAliasURL[]' size='20' />
            </td>
            <td width="200">
                <input type='text' name='inp_menuLinkActualURL[]' size='20' />
            </td>
        </tr>

<?php
if (!empty($menu['links']))
{
    foreach ($menu['links'] as $menuLink)
    {
?>
        <tr>
            <th align="center" width="100">
                <button type="button" class="alert only-img up" title="Move Up"></button>
                <button type="button" class="alert only-img down" title="Move Down"></button>
                <button type="button" class="alert only-img remove" title="Remove"></button>
            </td>
            <td width="200"><input type='text' name='inp_menuLinkDisplay[]' size='20' value="<?php echo $menuLink['display']; ?>"/></td>
            <td width="200"><input type='text' name='inp_menuLinkAliasURL[]' size='20' value="<?php echo $menuLink['url']; ?>"/></td>
            <td width="200"><input type='text' name='inp_menuLinkActualURL[]' size='20' value="<?php echo $menuLink['url']; ?>"/></td>
        </tr>
<?php
    }
}
?>

    </table>

</form>
