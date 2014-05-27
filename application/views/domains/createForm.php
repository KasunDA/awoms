<form id='<?php echo $formID; ?>'  method='POST'>
  <input type='hidden' name='step' value='2' />
  <input type='hidden' name='inp_domainID' value='<?php echo $domainID; ?>' />

  <table cellpadding='2' cellspacing='0'>

    <tr>
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
    
    <tr>
      <td>
        <!-- Domain -->
        Domain Name
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