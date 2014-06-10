            <div id="top">
                <div id='top-nav'>
                    <ul id="menu-top" class='menu-horizontal'>
                        
                        
<?php
    if (!empty($_SESSION['user_logged_in']))
    {
        // Authenticated User Navigation
        if ($_SESSION['user']['usergroup']['usergroupName'] == "Administrators")
        {
?>
                        <li><a href="/owners">Admin</a></li>
<?php
        }
        else
        {
?>
                        <li><a href="/owners">Owners Home</a></li>
<?php
        }
        
?>
                        <li><a href="/users/home"><?php echo $_SESSION['user']['userName']; ?></a></li>
                        <li><a href="/users/logout">Log Out</a></li>
<?php
        
    } else {
?>

                        <li>
                            <a href="https://www.facebook.com/hutno8" target="_parent">
                                <div class="fb-like" data-href="https://www.facebook.com/hutno8" data-send="false" data-layout="button_count" data-width="65" data-show-faces="false" data-font="segoe ui"></div>
                            </a>
                        </li>
                        <li><a href="">Shopping Cart (0)</a></li>
                        <li><a href="">Checkout</a></li>
<?php
    }
?>
                    </ul>
                </div>
            </div>