<?php
// Skip if in ajax mode
if (
  (!isset($_GET['m'])
    || strtolower($_GET['m']) != 'ajax')
  &&
  (!isset($_POST['m'])
  || strtolower($_POST['m']) != 'ajax'))
  {
    // Write Modal
    include(ROOT.DS.'application'.DS.'views'.DS.'articles'.DS.'writeForm.php');
  }
?>

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