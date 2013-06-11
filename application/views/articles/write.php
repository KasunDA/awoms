<!-- Results -->
<div id='results'>
  <?php
  if (isset($articleID)
    && $articleID != 'DEFAULT') {
    echo "
      Article (#".$articleID.") updated! (Copy #".$bodyContentID.") <a href='".BRANDURL."articles/view/".$articleID."'>View Article</a><hr />";
  }
  ?>
</div>

<!-- Template Output -->
<div id='view'>

  <form method='POST'>
    <input type='hidden' name='step' value='2' />
    <?php
    if (!isset($articleID)) {
      $articleID = 'DEFAULT';
    }?>
    <input type='hidden' name='inp_articleID' value='<?php echo $articleID; ?>' />
    
    <!-- Title -->
    Title
    <input type='text' name='inp_articleName' value='<?php
      if (isset($inp_articleName)) {
        echo $inp_articleName;
      }
    ?>' />
    <br />
    
    <!-- Short Desc -->
    Short Description
    <textarea name='inp_articleShortDescription'><?php
      if (isset($inp_articleShortDescription)) {
        echo $inp_articleShortDescription;
      }
  ?></textarea>
    <br />
    
    <!-- Long Desc -->
    Long Description
    <textarea name='inp_articleLongDescription'><?php
      if (isset($inp_articleLongDescription)) {
        echo $inp_articleLongDescription;
      }
  ?></textarea>
    <br />

    <!-- Body -->
    Body
    <textarea name='inp_articleBody'><?php
      if (isset($inp_articleBody)) {
        echo $inp_articleBody; // @todo [] NL to BR
      }
    ?></textarea>
    
    <!-- Adv Options -->
    <div id='advancedOptions'>
      
      Active
      <select name='inp_articleActive'>
        <option value='1'<?php
          if (!isset($inp_articleActive)
            || $inp_articleActive == 1) {
            echo ' selected';
          }
        ?>>Active</option>
        <option value='0'<?php
          if (isset($inp_articleActive)
            && $inp_articleActive == 0) {
            echo ' selected';
          }
        ?>>Inactive</option>
      </select>
      
    </div>
    
    <!-- Submit -->
    <button type='submit'>Go</button>
  </form>
  
<?php
// Can be used for multi-step 1.2..3..4.....5 inner-templates
/*
// Loads appropriate template file if exists otherwise goes straight to default template
$tplPath = ROOT.DS.'application/views'.DS.$this->controller.DS.$this->action.'.step'.$step.'.php';
if (is_file($tplPath)) {
  include($tplPath);
}
 * 
 */
?>

</div>