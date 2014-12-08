<?php
if ($this->step == 1)
{
    // Create/Update Form
    if (
        (!empty($formID))
        && /* ACL: Must have one of Create/Update/Delete access for form */
        (ACL::IsUserAuthorized($this->controller, "create")
        || ACL::IsUserAuthorized($this->controller, "update")
        || ACL::IsUserAuthorized($this->controller, "delete"))
    )
    {
        ?>
        <div class='step1_output'>
            <?php
            include(ROOT . DS . 'application' . DS . 'views' . DS . $this->controller . DS . 'createForm.php');
            ?>
        </div>
        <?php
    }
}
elseif ($this->step == 2)
{
    // Handle submitted form
    // Custom Label
    $label = preg_replace("/s$/", "", ucwords($this->controller));
    if ($label == "Usergroup")
    {
        $label = "Group";
    }

    // Success or Failure message
    if (is_bool($success) && $success == TRUE)
    {
        ?>
        <!-- Success Results -->
        <div id="divInnerResults" class="alert success">
            <?php echo "<p>" . $label . " " . $this->action . "d successfully!</p>"; ?>
            <META http-equiv='refresh' content='0;URL=<?php echo "/" . $this->controller . "/readall"; ?>'/>
        </div>
        <?php
    }
    else
    {
        ?>
        <!-- Failure Results -->
        <div id="divInnerResults" class="alert failure">
            <?php
            echo "<p>" . $label . " failed to " . $this->action . "!</p>";
            if (!empty($success))
            {
                echo "<p>" . $success . "</p>";
            } // Reason msg
            ?>
        </div>
        <?php
    }
    ?>

    <script>
        // Submitted form reloads list with new data which duplicates the forms etc.
        // This hides the orig readall data and button
        console.log('Hiding original step1 page output...');
        $('div.step1_output').hide();
        // Add Button
        $('#openModalCreate<?php echo ucfirst($this->controller); ?>').hide();
    </script>
    <?php
}
