<div id='divResults'></div>
<?php
if ($step == 1)
{
    if (!$success)
    {
?>
        <!-- Failure Results -->
        <div id="divInnerResults" class="failure">
          <?php echo "<h1>Login Failed!</h1>"; ?>
        </div>
<?php
        return;
    }

    require('loginForm.php');
}
elseif ($step == 2 && $success)
{
?>

<!-- Success Results -->
<div id="divInnerResults" class="success">
  <?php echo "<h1>Login Success!</h1>"; ?>
</div>

<?php
}