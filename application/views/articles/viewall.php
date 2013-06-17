<!-- Results -->
<div id='results'>
  <?php
    if (isset($resultsMsg)) {
      var_dump($resultsMsg);
    }
  ?>
</div>

<!-- Template Output -->
<div id='view'>
  
  <ul>
<?php
  for ($i=0; $i<count($articles); $i++) {
    $articleDate = str_replace('-','/',substr($articles[$i]['articleDatePublished'], 0, 10));
    $articleLink = BRANDURL.'articles/view/'.$articles[$i]['articleID'].'/'.$articleDate.'/'.str_replace(' ', '-', $articles[$i]['articleName']);
?>
    <li>
      <a href='<?=$articleLink;?>'>
        <?= $articles[$i]['articleName']; ?>
      </a>&nbsp;<small><cite><?= $articles[$i]['articleDatePublished']; ?></cite></small>
      <small><?=$articles[$i]['articleShortDescription']?></small>
    </li>
  
  <?php
  }
?>
</div>
  
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