<form id='<?php echo $formID; ?>'  method='POST'>
  <input type='hidden' name='step' value='2' />
  <input type='hidden' name='inp_pageID' value='<?php echo $pageID; ?>' />

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

    // Menu List - Non-Global-Admins (BrandID=1, Group=Admin) == limited by brand
    $class='hidden';
    if (!empty($menuChoiceList))
    {
        $class='';
    }
?>

    <tr>
      <td>
        Title
      </td>
      <td>
          <table>
              <tr>
                  <td colspan='2'>
                    <input type='text' id='inp_pageName' name='inp_pageName' value='<?php
                        if (isset($inp_pageName)) {
                          echo $inp_pageName;
                        }
                      ?>' size='60' />
                  </td>
              </tr>
              <tr class='<?php echo $class; ?>'>
                <td>
                  SEO Alias
                </td>
                <td>
                  Add to Menu
                </td>
              </tr>
              <tr class='<?php echo $class; ?>'>
                <td>
<?php
// Alias/Menu only on Create
if (!empty($menuChoiceList))
{
?>
                  <small class='muted'>/</small>&nbsp;<input type='text' id='inp_pageAlias' name='inp_pageAlias' value='<?php
                    if (isset($inp_pageAlias)) {
                      echo $inp_pageAlias;
                    }
                  ?>' size='20' />
                </td>
                <td>

                    <select name='inp_menuID'>
                    <?=$menuChoiceList;?>
                    </select>
<?php
}
?>
                </td>
              </tr>
          </table>
        
      </td>
    </tr>

    <tr>
        <td>
            Display Title
        </td>
        <td>
<?php
if (!empty($inp_pageShowTitle)) {
?>
            <input type="radio" name="inp_pageShowTitle" value="1" checked/>&nbsp;Yes
            <input type="radio" name="inp_pageShowTitle" value="0"/>&nbsp;No
<?php
} else {
?>
            <input type="radio" name="inp_pageShowTitle" value="1"/>&nbsp;Yes
            <input type="radio" name="inp_pageShowTitle" value="0" checked/>&nbsp;No
<?php
}
?>
        </td>
    </tr>
    
    <tr>
      <td>
          <!-- Short Desc -->
          Short Description
      </td>
      <td>
        <textarea id='inp_pageShortDescription' name='inp_pageShortDescription' cols='20' rows='3'><?php
          if (isset($inp_pageShortDescription)) {
            echo $inp_pageShortDescription;
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
        <textarea id='inp_pageLongDescription' name='inp_pageLongDescription' cols='40' rows='4'><?php
          if (isset($inp_pageLongDescription)) {
            echo $inp_pageLongDescription;
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
        <input type='text' id='inp_pageKeywords' name='inp_pageKeywords' value='<?php
          if (isset($inp_pageKeywords)) {
            echo $inp_pageKeywords;
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
        <textarea id='inp_pageBody' name='inp_pageBody' cols='60' rows='8' class="tinymce"><?php
        
          $tinyMCEInputID = "inp_pageBody";
        
          if (isset($inp_pageBody)) {
            echo $inp_pageBody['bodyContentText']; // @todo [] NL to BR ---- WYSIWYG!
          }
        ?></textarea>
      </td>
    </tr>

<?php
$pageJavaScript[] = file_get_contents(ROOT.DS.'application'.DS.'views'.DS.'pages'.DS.'pageJavaScript.js');
$class = "hidden";
if ($pageID == 'DEFAULT' || empty($page['pageJavaScript']))
{
    $class = "";
}
?>
    <tr>
        <td colspan="2">
            JavaScript Scripts to Include
            <br /><small class="muted">e.g. &lt;script src="/css/Bnd/myscript.js"&gt;&lt;/script&gt; <!-- <button type="button" id="cloneButton" class="alert only-img add" title="Add JavaScript"></button> -->
        </td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>
            <table cellpadding='2' cellspacing='0' id="appendToMe">
                <!-- Cloneable row -->
                <tr id='cloneMe' class="<?php echo $class; ?>">
                    <!--
                    <td align="center" width="100">
                        <button type="button" class="alert only-img remove" title="Remove"></button>
                    </td>
                    -->
                    <td colspan="2">
                        <input type='text' name='inp_pageJavaScript' size='60' />
                    </td>
                </tr>
                
<?php
if (!empty($page['pageJavaScript']))
{
    #foreach ($page['javascripts'] as $pageJS)
    #{
?>
                <tr>
                    <!--
                    <td align="center" width="100">
                        <button type="button" class="alert only-img remove" title="Remove"></button>
                    </td>
                    -->
                    <td colspan="2">
                        <input type='text' name='inp_pageJavaScript' value="<?php echo $page['pageJavaScript']; ?>" size='60' />
                    </td>
                </tr>
<?php
    #}
}
?>
                
            </table>
        </td>
    </tr>
    
    <tr>
      <td>
          Active
      </td>
      <td>
          <select id='inp_pageActive' name='inp_pageActive'>
            <option value='1'<?php
              if (!isset($inp_pageActive)
                || $inp_pageActive == 1) {
                echo ' selected';
              }
            ?>>Active</option>
            <option value='0'<?php
              if (isset($inp_pageActive)
                && $inp_pageActive == 0) {
                echo ' selected';
              }
            ?>>Inactive</option>
          </select>
      </td>
    </tr>

  </table>
</form>