<div id='divResults'></div>

<button type='button' class='openModal' name='frmCreateUser'>Create User</button>

<!-- Template Output -->
<div id='view'>
  
  <ul>
<?php
  for ($i=0; $i<count($users); $i++) {
    $userLink = BRANDURL.'users/view/'.$users[$i]['userID'].'/'.str_replace(' ', '-', $users[$i]['userName']);
?>
    <li>
      <a href='<?=$userLink;?>'>
        <?= $users[$i]['userName']; ?>
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
    include(ROOT.DS.'application'.DS.'views'.DS.'users'.DS.'createForm.php');
  }
?>