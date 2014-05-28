<?php
// Handle submitted form
if ($step == 2)
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
        <div id="divInnerResults" class="success">
          <?php echo "<h1>".$label." created successfully!</h1>"; ?>
        </div>
<?php
    }
    else
    {
?>
        <!-- Failure Results -->
        <div id="divInnerResults" class="failure">
          <?php
            echo "<h1>".$label." failed to create!</h1>";
            if (!empty($success)) { echo $success; } // Reason msg
          ?>
        </div>
<?php
        // Important return here to avoid list/form
        return;
    }
}


// Viewall
if (file_exists(ROOT.DS.'application'.DS.'views'.DS.BRAND.DS.$this->controller.DS.'viewall.php'))
{
    include(ROOT.DS.'application'.DS.'views'.DS.BRAND.DS.$this->controller.DS.'viewall.php');
}
elseif (file_exists(ROOT.DS.'application'.DS.'views'.DS.$this->controller.DS.'viewall.php'))
{
    include(ROOT.DS.'application'.DS.'views'.DS.$this->controller.DS.'viewall.php');
}
else
{
    include('viewall.php');
}