<form id='<?php echo $formID; ?>'  method='POST'>
    <input type='hidden' name='step' value='2' />
    <input type='hidden' name='inp_menuID' value='<?php echo $menuID; ?>' />
  
    <h1>Menu Information</h1>
    <table class="bordered">

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
                Login Restricted
            </td>
            <td>
<?php
$checked = "";
if (!empty($inp_menuRestricted)) {
    $checked = " checked";
}
?>
                <input type="hidden" id="inp_menuRestricted" name="inp_menuRestricted" value="0"/>
                <input type="checkbox" id="inp_menuRestricted" name="inp_menuRestricted" value="1"<?php echo $checked; ?>/>
            </td>
        </tr>
    
        <tr>
            <td>
                Menu Type
            </td>
            <td>
                <select id='inp_menuType' name='inp_menuType'/>
                    <?=$menuTypeChoiceList;?>
                </select>
            </td>
        </tr>
    
        <tr>
            <td>
                Menu Private Name
            </td>
            <td>
                <input type='text' id='inp_menuName' name='inp_menuName' value='<?php
        if (isset($inp_menuName)) {
            echo $inp_menuName;
        } else {
            echo "Default";
        }
        ?>' size='40' autocomplete="off" />
            </td>
        </tr>

        <tr>
            <td>
                Menu Title
            </td>
            <td>
                <input type='text' id='inp_menuTitle' name='inp_menuTitle' value='<?php
        if (isset($inp_menuTitle)) {
            echo $inp_menuTitle;
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

// Hide menu links on initial creation until:
// @TODO: Brand selection dynamically updates the cloneable row's Actual Page choice list
if ($menuID != 'DEFAULT')
{
?>
    <h1>Menu Links</h1>
    <table class="bordered">
        <tr>
            <td>
                
    <!-- Menu Links -->
    <table class="no-border">
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
                Actual Page
            </th>
            <th width="210">
                or URL
            </th>
        </tr>
    </table>

    <table id="menuLinksTable">
        
        <!-- Cloneable row -->
        <tr>
            <td align="center" width="120">
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
                <select name="inp_menuLinkActualPageID[]" style="max-width:200px">
                    <option value="0">-- Select --</option>
                    <?php if (!empty($menu['pageChoiceList'])) { echo $menu['pageChoiceList']; } ?>
                </select>
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
            <th align="center" width="120">
                <button type="button" class="alert only-img up" title="Move Up"></button>
                <button type="button" class="alert only-img down" title="Move Down"></button>
                <button type="button" class="alert only-img remove" title="Remove"></button>
            </td>
            <td width="200"><input type='text' name='inp_menuLinkDisplay[]' size='20' value="<?php echo $menuLink['display']; ?>"/></td>
            <td width="200"><input type='text' name='inp_menuLinkAliasURL[]' size='20' value="<?php echo $menuLink['url']; ?>"/></td>
            <td width="200">
                <select name="inp_menuLinkActualPageID[]" style="max-width:200px">
                    <option value="0">-- Select --</option>
                    <?php if (!empty($menuLink['pageChoiceList'])) { echo $menuLink['pageChoiceList']; } ?>
                </select></td>
            <td width="200"><input type='text' name='inp_menuLinkActualURL[]' size='20' value="<?php
                if (!empty($menuLink['actualURL'])) {
                    echo $menuLink['actualURL'];
                }
                ?>"/></td>
        </tr>
        
<?php
    }
}
?>

    </table>

    
            </td>
        </tr>
    </table>
    
<?php
}
?>

    <!-- Form Action Buttons -->
    <table class="form_actions">
        <tr>
            <td>
                <?php
                if ($this->action != "create"
                        && ACL::IsUserAuthorized($this->controller, "delete")) {
                ?>
                <button type="button" class="callAPI button_delete" name="<?=$this->controller;?>" value="delete">
                    Delete
                </button>
                <?php
                }
                ?>
                <button type="button" class="callAPI button_cancel" name="<?=$this->controller;?>" value="cancel">
                    Cancel
                </button>
                <button type="button" class="callAPI button_save" name="<?=$this->controller;?>" value="<?=$this->action;?>">
                    Save
                </button>
            </td>
        </tr>
    </table>

    
</form>
