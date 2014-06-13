<form id='<?php echo $formID; ?>'  method='POST'>
  <input type='hidden' name='step' value='2' />
  <input type='hidden' name='inp_articleID' value='<?php echo $articleID; ?>' />
  
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
        <!-- Title -->
        Title
      </td>
      <td>
        <input type='text' id='inp_articleName' name='inp_articleName' value='<?php
          if (isset($inp_articleName)) {
            echo $inp_articleName;
          }
        ?>' size='60' />
      </td>
    </tr>

    <tr>
      <td>
          <!-- Short Desc -->
          Short Description
      </td>
      <td>
        <textarea id='inp_articleShortDescription' name='inp_articleShortDescription' cols='20' rows='3'><?php
          if (isset($inp_articleShortDescription)) {
            echo $inp_articleShortDescription;
          }
      ?></textarea>
      </td>
    </tr>

    <tr>
      <td>
        <!-- Long Desc -->
        Long Description
      </td>
      <td>
        <textarea id='inp_articleLongDescription' name='inp_articleLongDescription' cols='40' rows='4'><?php
          if (isset($inp_articleLongDescription)) {
            echo $inp_articleLongDescription;
          }
      ?></textarea>
      </td>
    </tr>

    <!-- Keywords
    <tr>
      <td>
        Keywords
      </td>
      <td>
        <input type='text' id='inp_articleKeywords' name='inp_articleKeywords' value='<?php
          if (isset($inp_articleKeywords)) {
            echo $inp_articleKeywords;
          }
        ?>' size='60' placeholder='Coming Soon!' disabled />
      </td>
    </tr>
    -->
    
    <tr>
      <td>
        <!-- Body -->
        Body
      </td>
      <td>
        <textarea id='inp_articleBody' name='inp_articleBody' cols='60' rows='8' class="tinymce"><?php
        
            $tinyMCEInputID = "inp_articleBody";
            
          if (isset($inp_articleBody)) {
            echo $inp_articleBody['bodyContentText']; // @todo [] NL to BR
          }
        ?></textarea>
      </td>
    </tr>

    <tr>
      <td>
          Active
      </td>
      <td>
          <select id='inp_articleActive' name='inp_articleActive'>
            <option value='1'<?php
              if (!isset($inp_articleActive)
                || $inp_articleActive == 1) {
                echo ' selected';
              }
            ?>>Active</option>
            <option value='0'<?php
              if (isset($inp_articleActive)
                && $inp_articleActive == 0) {
                echo ' selected';
              }
            ?>>Inactive</option>
          </select>
      </td>
    </tr>

  </table>
</form>