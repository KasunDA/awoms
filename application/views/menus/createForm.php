<form id='<?php echo $formID; ?>'  method='POST'>
  <input type='hidden' name='step' value='2' />
  <input type='hidden' name='inp_linkID' value='<?php echo $linkID; ?>' />

  <table cellpadding='2' cellspacing='0'>

    <tr>
      <td>
        Display
      </td>
      <td>
        <input type='text' id='inp_linkDisplay' name='inp_linkDisplay' value='<?php
          if (isset($inp_linkDisplay)) {
            echo $inp_linkDisplay;
          }
        ?>' size='60' />
      </td>
    </tr>

    <tr>
      <td>
        URL
      </td>
      <td>
        <input type='text' id='inp_linkURL' name='inp_linkURL' value='<?php
          if (isset($inp_linkURL)) {
            echo $inp_linkURL;
          }
        ?>' size='60' />
      </td>
    </tr>

  </table>
</form>