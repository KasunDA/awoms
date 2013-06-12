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
?>
    <li>
      <a href='<?= BRANDURL; ?>articles/view/<?= $articles[$i]['articleID']; ?>/<?= str_replace(' ', '-', $articles[$i]['articleName']); ?>'>
        <?= $articles[$i]['articleName']; ?>
      </a>&nbsp;<small><cite><?= $articles[$i]['articleDatePublished']; ?></cite></small>
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