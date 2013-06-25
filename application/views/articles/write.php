<!-- Results -->
<div id='divResults'>
  <?php
  if (isset($articleID)
    && $articleID != 'DEFAULT') {
    echo "
      Article (#".$articleID.") updated! (Copy #".$bodyContentID.") <a href='".BRANDURL."articles/view/".$articleID."/".str_replace(' ', '-', $inp_articleName)."'>View Article</a><hr />";
  }
  ?>
</div>

<!-- Template Output -->
<div id='view'>
  <?php
    include('writeForm.php');
  ?>
</div>