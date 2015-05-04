   <form id='<?php echo $formID; ?>'  method='POST'>
  <input type='hidden' name='step' value='2' />
  <input type='hidden' name='inp_pageID' value='<?php echo $pageID; ?>' />

    <h1>Page Information</h1>
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

    // Menu List - Non-Global-Admins (BrandID=1, Group=Admin) == limited by brand
    $class='hidden';
    if (!empty($menuChoiceList))
    {
        $class='';
    }
?>

    <tr>
      <td>
        Private Name
        <p class="muted">This is used to help you identify pages and is never seen by the public.
      </td>
      <td>
            <input type='text' id='inp_pagePrivateName' name='inp_pagePrivateName' value='<?php
            if (isset($inp_pagePrivateName)) {
                echo $inp_pagePrivateName;
            }
            ?>' size='60' />
      </td>
    </tr>

    <tr>
      <td>
        Page Heading
        <p class="muted">This is used as the page heading, if set.
      </td>
      <td>
            <input type='text' id='inp_pageHeading' name='inp_pageHeading' value='<?php
            if (isset($inp_pageHeading)) {
                echo $inp_pageHeading;
            }
            ?>' size='60' />
      </td>
    </tr>

    <tr>
      <td>
        Meta Title
        <p class="muted">Title for the page that will appear in SEO results and in the browser. If it is not set here, it will use the one set at the Brand level.</p>
      </td>
      <td>
          <table <?php if (!empty($class)) { echo "class='no-border'"; } ?>>
              <tr>
                  <td colspan='2'>
                    <input type='text' id='inp_pageMetaTitle' name='inp_pageMetaTitle' value='<?php
                        if (isset($inp_pageMetaTitle)) {
                          echo $inp_pageMetaTitle;
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

                    <select name='inp_menuID' style="width:400px">
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
          <!-- Meta Desc -->
          Meta Description
          <p class="muted">Meta Description may appear in search results. If it is not set here, it will use the one set at the Brand level.</p>
      </td>
      <td>
        <textarea id='inp_pageMetaDescription' name='inp_pageMetaDescription' cols='40' rows='4'><?php
          if (isset($inp_pageMetaDescription)) {
            echo $inp_pageMetaDescription;
          }
      ?></textarea>
      </td>
    </tr>

    <!-- Meta Keywords -->
    <tr>
      <td>
        Meta Keywords
        <p class='muted'>Meta Keywords (comma separated) may appear in search results. If it is not set here, it will use the one set at the Brand level.</p>
      </td>
      <td>
        <input type='text' id='inp_pageMetaKeywords' name='inp_pageMetaKeywords' value='<?php
          if (isset($inp_pageMetaKeywords)) {
            echo $inp_pageMetaKeywords;
          }
        ?>' size='60' />
      </td>
    </tr>

    <tr>
        <td>
            Login Restricted
            <p class="muted">User (e.g. Store Owner) must be logged in to view this page if enabled.</p>
        </td>
        <td>
<?php
if (!empty($inp_pageRestricted)) {
?>
            <input type="radio" name="inp_pageRestricted" value="1" checked/>&nbsp;Yes
            <input type="radio" name="inp_pageRestricted" value="0"/>&nbsp;No
<?php
} else {
?>
            <input type="radio" name="inp_pageRestricted" value="1"/>&nbsp;Yes
            <input type="radio" name="inp_pageRestricted" value="0" checked/>&nbsp;No
<?php
}
?>
        </td>
    </tr>

    <tr>
      <td>
        <!-- Body -->
        Body
      </td>
      <td>
        <textarea id='inp_pageBody' name='inp_pageBody' cols='60' rows='8' class="tinymce" style="min-height: 445px; min-width: 680px;"><?php
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
            <p class="muted">
                Inline or external JavaScript to include on this page for full custom control or special scripts.<br/>
                <small>
                    e.g. &lt;script src="/js/MyBnd/myscript.js"&gt;&lt;/script&gt;
                    <br/>
                    or &lt;script&gt;$('#main-container').hide();&lt;/script&gt;
                    <!-- <button type="button" id="cloneButton" class="alert only-img add" title="Add JavaScript"></button> -->
                </small>
            </p>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <table cellpadding='2' cellspacing='0' id="appendToMe">
                <!-- Cloneable row -->
                <tr id='cloneMe' class="<?php echo $class; ?>">
                    <!--
                    <td align="center" width="100">
                        <button type="button" class="alert only-img remove" title="Remove"></button>
                    </td>
                    -->
                    <td colspan="2">
                        <input type='text' name='inp_pageJavaScript' size='85' />
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