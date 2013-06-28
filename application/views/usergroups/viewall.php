<div id='divResults'></div>

<button type='button' class='openModal' name='frmCreateUsergroup'>Create Usergroup</button>

<!-- Template Output -->
<div id='view'>
  
  <ul>
<?php
  for ($i=0; $i<count($usergroups); $i++) {
    $usergroupLink = DOMAINURL.'usergroups/view/'.$usergroups[$i]['usergroupID'].'/'.str_replace(' ', '-', $usergroups[$i]['usergroupName']);
?>
    <li>
      <a href='<?=$usergroupLink;?>'>
        <?= $usergroups[$i]['usergroupName']; ?>
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
    include(ROOT.DS.'application'.DS.'views'.DS.'usergroups'.DS.'createForm.php');
  }
?>