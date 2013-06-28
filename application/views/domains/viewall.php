<div id='divResults'></div>

<button type='button' class='openModal' name='frmCreateDomain'>Create Domain</button>

<!-- Template Output -->
<div id='view'>
  
  <ul>
<?php
  for ($i=0; $i<count($domains); $i++) {
    $domainLink = DOMAINURL.'domains/view/'.$domains[$i]['domainID'].'/'.str_replace(' ', '-', $domains[$i]['domainName']);
?>
    <li>
      <a href='<?=$domainLink;?>'>
        <?= $domains[$i]['domainName']; ?>
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
    include(ROOT.DS.'application'.DS.'views'.DS.'domains'.DS.'createForm.php');
  }
?>