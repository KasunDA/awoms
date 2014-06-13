<form id='<?php echo $formID; ?>'  method='POST'>
  <input type='hidden' name='step' value='2' />
  <input type='hidden' name='inp_domainID' value='<?php echo $domainID; ?>' />

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
        <!-- Domain -->
        Domain Name
        <small class='muted'>http://</small>
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
</form>