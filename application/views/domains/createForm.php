<form id='<?php echo $formID; ?>'  method='POST'>
  <input type='hidden' name='step' value='2' />
  <input type='hidden' name='inp_domainID' value='<?php echo $domainID; ?>' />

  <h1>Domain Information</h1>
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
    
    // Store List - Non-Global-Admins (BrandID=1, Group=Admin) == limited by brand
    if (!empty($storeChoiceList))
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
        Store
        <p class='muted'>You can assign a domain name to a specific store to be brought to that store when visited</p>
      </td>
      <td>
        <select name='inp_storeID'>
          <?=$storeChoiceList;?>
        </select>
      </td>
    </tr>
<?php
    }
?>
    
    <tr>
      <td>
        <!-- Domain -->
        Domain Name
        <small class='muted'>http://www.<br />Exclude www. prefix</small>
        
      </td>
      <td>
        <input type='text' id='inp_domainName' name='inp_domainName' value='<?php
          if (isset($inp_domainName)) {
            echo $inp_domainName;
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