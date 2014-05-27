<?php
if ($step == 2)
{
?>
    <!-- Results -->
    <div id="divInnerResults" class="success">
      <?php
        $label = trim(ucfirst($this->controller), "s");
        if ($label == "Usergroup")
        {
            $label = "Group";
        }
        
        echo "<h1>".$label." updated successfully!</h1>";
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
