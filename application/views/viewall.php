<div class="main_viewall">
    <div id='divResults'></div>
    <div class="main_output">
        
<?php
$label = ucwords(trim($this->controller, "s"));
if ($label == "Usergroup") { $label = "Group"; }
if ($this->action == 'edit')
{
?>
        <button type='button' onClick="location.href='/<?php echo $this->controller."/create"; ?>'">Add <?php echo $label; ?></button>
<?php
} else {
?>
        <button type='button' id='openModalCreate<?php echo ucwords($this->controller); ?>' name='frmCreate<?php echo ucwords($this->controller); ?>' class='openModal' >Add <?php echo $label; ?></button>
<?php
}

// Load custom template if exists
if (file_exists(ROOT.DS.'application'.DS.'views'.DS.BRAND.DS.$this->controller.DS.'viewall_list.php'))
{
    include(ROOT.DS.'application'.DS.'views'.DS.BRAND.DS.$this->controller.DS.'viewall_list.php');
}
elseif (file_exists(ROOT.DS.'application'.DS.'views'.DS.$this->controller.DS.'viewall_list.php'))
{
    include(ROOT.DS.'application'.DS.'views'.DS.$this->controller.DS.'viewall_list.php');
}
else
{
    // Default List Template
?>
        <!-- Output -->
        <ul>
<?php
    $c = $this->controller;
    $items = $$c;
    $lbl = trim(strtolower($this->controller), "s");
    for ($i=0; $i<count($items); $i++) {
      $editLink = BRAND_URL.$this->controller.'/edit/'.$items[$i][$lbl.'ID'].'/'.str_replace(' ', '-', $items[$i][$lbl.'Name']);
?>
            <li>
              <a href="<?=$editLink;?>">
                <?= $items[$i][$lbl.'Name']; ?>
              </a>
            </li>
<?php
    }
?>
        </ul>
<?php
}
?>
        
    </div>
</div>

<?php
// Create Modal (Skip if in ajax mode)
if (
    (!isset($_GET['m'])
        || strtolower($_GET['m']) != 'ajax')
  &&
    (!isset($_POST['m'])
        || strtolower($_POST['m']) != 'ajax'))
  {
    // Create Modal
    include(ROOT.DS.'application'.DS.'views'.DS.$this->controller.DS.'createForm.php');

    // Open Modal if on Create page
    if (strtolower($this->action) == 'create')
    {
        $pageJavaScript[] = "$('#openModalCreate" . ucfirst($this->controller) . "').trigger('click');";
    }
    
    // Open Modal if on Edit page
    elseif (strtolower($this->action) == 'edit'
            && $this->step == 1)
    {
        $pageJavaScript[] = "
        var modalID = 'frmEdit".ucfirst($this->controller)."';

        // Open Dialog
        $('#' + modalID).removeClass('hidden');
        $('#' + modalID).dialog('open');";
    }
  }

// Submitted form reloads list with new data which duplicates the forms etc.
// This hides the orig viewall data and button
if ($this->step == 2)
{
?>
    <script>
        console.debug('Hiding original page output...');
        $('div.main_viewall:first > div.main_output').hide();
        $('#openModalCreate<?php echo ucfirst($this->controller); ?>').hide();
    </script>
<?php
}
