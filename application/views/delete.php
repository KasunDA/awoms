<?php
// Handle submitted form
if ($this->step == 2)
{
    // Custom Label
    $label = trim(ucfirst($this->controller), "s");
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
          <?php echo "<p>".$label." deleted successfully!</p>"; ?>
        </div>
<?php
    }
    else
    {
?>
        <!-- Failure Results -->
        <div id="divInnerResults" class="alert failure">
          <?php
            echo "<p>".$label." failed to delete!</p>";
            if (!empty($success)) { echo "<p>".$success."</p>"; } // Reason msg
          ?>
        </div>
<?php
        // Important return here to avoid list/form
        return;
    }
}


// Readall
if (file_exists(ROOT.DS.'application'.DS.'views'.DS.BRAND.DS.$this->controller.DS.'readall.php'))
{
    include(ROOT.DS.'application'.DS.'views'.DS.BRAND.DS.$this->controller.DS.'readall.php');
}
elseif (file_exists(ROOT.DS.'application'.DS.'views'.DS.$this->controller.DS.'readall.php'))
{
    include(ROOT.DS.'application'.DS.'views'.DS.$this->controller.DS.'readall.php');
}
else
{
    include('readall.php');
}