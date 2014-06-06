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
          <?php echo "<p><strong>Login Failed!</strong> <a href='/users/password'>Forgot Password?</a></p>"; ?>
        </div>
<?php
    }
    // Successful log OUT message
    elseif (!empty($logoutSuccess))
    {
?>
        <!-- Logout Success -->
        <div id="divInnerResults" class="alert success">
          <?php echo "<p><strong>Log Out Success!</strong></p>"; ?>
        </div>        
<?php
    // 403 landing requesting authentication
    } elseif (!empty($access)) {
?>
        <!-- 403 Results -->
        <div id="divInnerResults" class="alert failure">
          <?php echo "<p><strong>Access Denied!</strong> Please Log In below.</p>"; ?>
        </div>

<?php
    }
    
    /* Show Form */

    // Views folder
    $viewsFolder = ROOT . DS . 'application' . DS . 'views' . DS;
    // Possible locations
    $fileLocations = array();
    $fileLocations[] = $viewsFolder . 'templates' . DS . BRAND_LABEL . DS . BRAND_THEME . DS . $_SESSION['controller'] . DS . 'loginForm.php';
    $fileLocations[] = $viewsFolder . 'templates' . DS . BRAND_LABEL . DS . BRAND_THEME . DS . 'loginForm.php';
    $fileLocations[] = $viewsFolder . 'loginForm.php';
    foreach ($fileLocations as $fileLoc)
    {
        if (file_exists($fileLoc))
        {
            include($fileLoc);
            break;
        }
    }
}
elseif ($step == 2 && $success)
{
?>
    <!-- Success Results -->
    <div id="divInnerResults" class="alert success">
      <?php echo "<p><strong>Login Success!</strong></p>"; ?>
    </div>

<?php
    // Redirect on successful login
    if (!empty($returnURL)) {
?>
    <META http-equiv="refresh" content="0;URL=<?php echo $returnURL; ?>">
<?php
    }
}