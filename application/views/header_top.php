            <div id="top">
                <div id='top-nav'>
                    <ul id="menu-top" class='menu-horizontal'>
<?php
if (!empty($_SESSION['user_logged_in']))
{
    // Authenticated User Navigation
?>
                        <li><a href="/users/logout">Log Out</a></li>
<?php
} else {
?>
                        <li><a href="/users/login">Log In</a></li>
<?php
}
?>
                    </ul>
                </div>
            </div>