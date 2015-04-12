<div id='divResults'></div>

<?php
if ($step == 1)
{
    // Lost Password Request form displayed
    $fileLocations = Utility::getTemplateFileLocations('passwordForm');
    foreach ($fileLocations as $fileLoc){include($fileLoc);}
}
elseif ($step == 2 && $success)
{
    // Lost Password Request form submitted - Successfully sent Email
?>
    <div id="divInnerResults" class="alert success">
      <?php echo "<p><strong>Password reset email sent! Please allow up to 15 minutes for greylisting.</strong></p>"; ?>
    </div>
<?php
}
elseif ($step == 2 && !$success)
{
    // Lost Password Request form submitted - Error sending Email
?>
    <div id="divInnerResults" class="alert failure">
      <?php echo "<p><strong>Error sending Password reset email! Please try again later.</strong></p>"; ?>
    </div>
<?php
}
elseif ($step == 3 && $success)
{
    // Reset Password form displayed - Valid Code
    $fileLocations = Utility::getTemplateFileLocations('passwordResetForm');
    foreach ($fileLocations as $fileLoc){include($fileLoc);}
}
elseif ($step == 3 && !$success)
{
    // Reset Password form displayed - Invalid or Expired Code
?>
    <div id="divInnerResults" class="alert failure">
      <?php echo "<p><strong>Unrecognized or Expired code! Please try the Lost Password form again.</strong></p>"; ?>
    </div>
<?php
}
elseif ($step == 4 && $success)
{
    // Reset Password form submitted - Successfully changed password
?>
    <div id="divInnerResults" class="alert success">
      <?php echo "<p><strong>Password reset successfully! Please log in with your new password.</strong></p>"; ?>
    </div>
<?php
}
elseif ($step == 4 && !$success)
{
    // Reset Password form submitted - Error changing password or confirm field did not match password field
?>
    <div id="divInnerResults" class="alert failure">
      <?php echo "<p><strong>Error resetting Password! Please try again ensuring you type the same password twice to confirm.</strong></p>"; ?>
    </div>

<?php

    // Reset Password form displayed - Valid Code
    $fileLocations = Utility::getTemplateFileLocations('passwordResetForm');
    foreach ($fileLocations as $fileLoc){include($fileLoc);}

}
