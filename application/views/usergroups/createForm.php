<form id='<?php echo $formID; ?>'  method='POST'>
  <input type='hidden' name='step' value='2' />
  <input type='hidden' name='inp_usergroupID' value='<?php echo $usergroupID; ?>' />

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
</form>