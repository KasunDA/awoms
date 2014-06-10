
<h1>GPFC Administration</h1>

<?php
if (empty($_SESSION['user_logged_in']))
{
    echo "<p>Please click <a href='/users/login'>here</a> to log in.</p>";
    
} else {
    
    echo "<p>Welcome back <strong>".$_SESSION['sessionSaveTime']."</strong>!</p>";
    
}
?>