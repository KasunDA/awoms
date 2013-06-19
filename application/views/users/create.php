<!-- Results -->
<div id='results'>
  <?php
  if (isset($userID)
    && $userID != 'DEFAULT') {
    echo "User (#".$userID.") updated!";
  }
  ?>
</div>
