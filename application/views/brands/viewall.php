<div id='divResults'></div>

<button type='button' class='openModal' name='frmCreateBrand'>Create Brand</button>

<!-- Template Output -->
<div id='view'>
  
  <ul>
<?php
  for ($i=0; $i<count($brands); $i++) {
    $brandLink = DOMAINURL.'brands/view/'.$brands[$i]['brandID'].'/'.str_replace(' ', '-', $brands[$i]['brandName']);
?>
    <li>
      <a href='<?=$brandLink;?>'>
        <?= $brands[$i]['brandName']; ?>
      </a>
    </li>
  
  <?php
  }
?>
  </ul>

</div>

<?php
// Write Modal
// Skip if in ajax mode
if (
  (!isset($_GET['m'])
    || strtolower($_GET['m']) != 'ajax')
  &&
  (!isset($_POST['m'])
  || strtolower($_POST['m']) != 'ajax'))
  {
    // Write Modal
    include(ROOT.DS.'application'.DS.'views'.DS.'brands'.DS.'createForm.php');
  }
?>