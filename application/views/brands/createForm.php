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
                <p class="muted">This unique brand identifier is used for Theme folders and should not contain spaces, e.g. AWOMS
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