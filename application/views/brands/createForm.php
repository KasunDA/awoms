<form id='<?php echo $formID; ?>'  method='POST'>
    <input type='hidden' name='step' value='2' />
    <input type='hidden' name='inp_brandID' value='<?php echo $brandID; ?>' />

    <h1>Brand Information</h1>
    <table class="bordered">

        <tr>
            <td>
                <!-- Brand -->
                Brand Name
            </td>
            <td>
                <input type='text' id='inp_brandName' name='inp_brandName' value='<?php
                if (isset($inp_brandName))
                {
                    echo $inp_brandName;
                }
                ?>' size='60' />
            </td>
        </tr>

        <tr>
            <td>
                Brand Label
                <p class="muted">This unique brand identifier is used for Theme folders and should not contain spaces, e.g. GPFC
            </td>
            <td>
                <input type='text' id='inp_brandLabel' name='inp_brandLabel' value='<?php
                if (isset($inp_brandLabel))
                {
                    echo $inp_brandLabel;
                }
                ?>' size='60' />
            </td>
        </tr>

        <tr>
            <td>
                Default Meta Title
                <p class="muted">This will be used for the default HTML Meta Title unless overridden by page settings, e.g. Goin' Postal, Low cost shipping and packaging franchise.</p>
            </td>
            <td>
                <input type='text' id='inp_brandMetaTitle' name='inp_brandMetaTitle' value='<?php
                if (isset($inp_brandMetaTitle))
                {
                    echo $inp_brandMetaTitle;
                }
                ?>' size='60' />
            </td>
        </tr>

        <tr>
            <td>
                Default Meta Description
                <p class="muted">Meta Description may appear in search results.</p>
            </td>
            <td>
              <textarea id='inp_brandMetaDescription' name='inp_brandMetaDescription' cols='40' rows='4'><?php
                if (isset($inp_brandMetaDescription)) {
                  echo $inp_brandMetaDescription;
                }
            ?></textarea>
            </td>
          </tr>

          <tr>
            <td>
              Default Meta Keywords
              <p class='muted'>Meta Keywords (comma separated) may appear in search results.</p>
            </td>
            <td>
              <input type='text' id='inp_brandMetaKeywords' name='inp_brandMetaKeywords' value='<?php
                if (isset($inp_brandMetaKeywords)) {
                  echo $inp_brandMetaKeywords;
                }
              ?>' size='60' />
            </td>
          </tr>

<?php
if ($brandID != "DEFAULT")
{
    // Only on Edit form
?>
        <tr>
            <td>
                Brand Fav Icon
                <p class="muted">This shows in most browsers next in the URL.</p>
            </td>
            <td>
                <input type='text' id='inp_brandFavIcon' name='inp_brandFavIcon' value='<?php
                if (isset($inp_brandFavIcon))
                {
                    echo $inp_brandFavIcon;
                }
                ?>' size='60' />

    <a href="/filemanager/dialog.php?type=0" class="btn iframe-btn" type="button">Choose</a>
    <p class="muted">Click Choose then upload or find an existing favicon.ico file to use. Right click on the file and choose 'Show URL'. <b>Copy the URL</b> then close then file manager. Pase the URL into the field here then Save.</p>
<?php
    $pageJavaScript[] = "
        $('.iframe-btn').fancybox({
            'width'		: 900,
            'height'	: 600,
            'type'		: 'iframe',
            'autoSize'  : false
        });";
?>
            </td>
        </tr>
<?php
}
?>
        <tr>
            <td>
                Brand Email
            </td>
            <td>
                <input type='text' id='inp_brandEmail' name='inp_brandEmail' value='<?php
                if (isset($inp_brandEmail))
                {
                    echo $inp_brandEmail;
                }
                ?>' size='60' />
            </td>
        </tr>

        <?php
        if ($brandID == "DEFAULT")
        {
            // Create form
            echo "<input type='hidden' name='inp_activeTheme' value='default' />";
        }
        else
        {
            // Update form
            ?>
            <tr>
                <td>
                    Active Theme
                    <p class="muted">This theme name is used to decide which templates to use, e.g. default or xmas</p>
                </td>
                <td>
                    <input type='text' id='inp_activeTheme' name='inp_activeTheme' value='<?php
            if (isset($inp_activeTheme))
            {
                echo $inp_activeTheme;
            }
            ?>' size='60' />
                </td>
            </tr>
    <?php
}
?>

    </table>

    <!-- Form Action Buttons -->
    <table class="form_actions">
        <tr>
            <td>
                <?php
                if ($this->action != "create"
                    && ACL::IsUserAuthorized($this->controller, "delete"))
                {
                    ?>
                    <button type="button" class="callAPI button_delete" name="<?= $this->controller; ?>" value="delete">
                        Delete
                    </button>
    <?php
}
?>
                <button type="button" class="callAPI button_cancel" name="<?= $this->controller; ?>" value="cancel">
                    Cancel
                </button>
                <button type="button" class="callAPI button_save" name="<?= $this->controller; ?>" value="<?= $this->action; ?>">
                    Save
                </button>
            </td>
        </tr>
    </table>

</form>