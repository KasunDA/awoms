<h1>Goin' Postal Home Page</h1>

<?php

if (empty($_SESSION['user_logged_in']))
{
    
    echo "Welcome <strong>guest</strong>!";
    
} else {
    
    echo "Welcome back <strong>".$_SESSION['sessionSaveTime']."</strong>!";
    
}

?>