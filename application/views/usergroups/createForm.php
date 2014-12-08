<form id='<?php echo $formID; ?>'  method='POST'>
  <input type='hidden' name='step' value='2' />
  <input type='hidden' name='inp_usergroupID' value='<?php echo $usergroupID; ?>' />

    <h1>Group Information</h1>
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
        <!-- Usergroup -->
        Group Name
      </td>
      <td>
        <input type='text' id='inp_usergroupName' name='inp_usergroupName' value='<?php
          if (isset($inp_usergroupName)) {
            echo $inp_usergroupName;
          }
        ?>' size='60' />
      </td>
    </tr>

  </table>
  

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