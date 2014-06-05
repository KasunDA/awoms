<div id='divResults'></div>

<?php
if ($step == 1)
{
    // Failed log IN message
    if (!$success)
    {
?>
        <!-- Failure Results -->
        <div id="divInnerResults" class="alert failure">
          <?php echo "<p>Login Failed! <a href='/users/password'>Forgot Password?</a></p>"; ?>
        </div>
<?php
    }
    // Successful log OUT message
    elseif (!empty($logoutSuccess))
    {
?>
        <!-- Logout Success -->
        <div id="divInnerResults" class="alert success">
          <?php echo "<p>Log Out Success!</p>"; ?>
        </div>        
<?php
    }
    
    // Show Form
    require('loginForm.php');
}
elseif ($step == 2 && $success)
{
?>
    <!-- Success Results -->
    <div id="divInnerResults" class="alert success">
      <?php echo "<p>Login Success!</p>"; ?>
    </div>

<?php
    // Redirect on successful login
    if (!empty($returnURL)) {
?>
    <META http-equiv="refresh" content="1;URL=<?php echo $returnURL; ?>">
<?php
    }
}