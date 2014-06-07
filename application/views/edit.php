<?php
if ($this->step == 2)
{
?>
    <!-- Results -->
    <div id="divInnerResults" class="alert success">
      <?php
        $label = trim(ucfirst($this->controller), "s");
        if ($label == "Usergroup")
        {
            $label = "Group";
        }
        
        echo "<p>".$label." updated successfully!</p>";
      ?>
    </div>
<?php
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
