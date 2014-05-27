<form id='<?php echo $formID; ?>'  method='POST'>
  <input type='hidden' name='step' value='2' />
  <input type='hidden' name='inp_brandID' value='<?php echo $brandID; ?>' />

  <table cellpadding='2' cellspacing='0'>

    <tr>
      <td>
        <!-- Brand -->
        Brand Name
      </td>
      <td>
        <input type='text' id='inp_brandName' name='inp_brandName' value='<?php
          if (isset($inp_brandName)) {
            echo $inp_brandName;
          }
        ?>' size='60' />
      </td>
    </tr>
    
    <tr>
      <td>
        Brand Label
      </td>
      <td>
        <input type='text' id='inp_brandLabel' name='inp_brandLabel' value='<?php
          if (isset($inp_brandLabel)) {
            echo $inp_brandLabel;
          }
        ?>' size='60' />
      </td>
    </tr>

  </table>
</form>