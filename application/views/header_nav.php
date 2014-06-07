            <header>
                
<?php
    // Home page has different header (doesnt have bottom to hang over wowslider images)
    if ($_SESSION['action'] == 'home') { $class = "header"; } else { $class = "header-full"; }
?>
                <div class="<?php echo $class; ?>">
                    <nav>

                        <!-- CSS Menu from http://cssmenumaker.com/builder/1917619 -->
                        <div id='cssmenu'>
                            <ul>
<?php
if (!empty($_SESSION['user_logged_in']))
{
    // Authenticated User Navigation
?>
                                <li class='has-sub'>
                                    <a href='<?php echo BRAND_URL; ?>'>Admin</a>
                                        <ul>

                                            <li class='has-sub'>
                                                <a href='<?php echo BRAND_URL; ?>brands/viewall'>Brands</a>
                                                <ul>
                                                    <li class='has-sub'>
                                                        <a href='<?php echo BRAND_URL; ?>brands/viewall'>Brands</a>
                                                        <ul>
                                                            <li><a href='<?php echo BRAND_URL; ?>brands/create'>Add Brand</a></li>
                                                        </ul>
                                                    </li>
                                                    <li class='has-sub'>
                                                        <a href='<?php echo BRAND_URL; ?>domains/viewall'>Domains</a>
                                                        <ul>
                                                            <li><a href='<?php echo BRAND_URL; ?>domains/create'>Add Domain</a></li>
                                                        </ul>
                                                    </li>
                                                </ul>
                                            </li>
                                            
                                            <li class='has-sub'>
                                                <a href='<?php echo BRAND_URL; ?>users/viewall'>Users</a>
                                                <ul>
                                                    <li class='has-sub'>
                                                        <a href='<?php echo BRAND_URL; ?>users/viewall'>Users</a>
                                                        <ul>
                                                            <li><a href='<?php echo BRAND_URL; ?>users/create'>Add User</a></li>
                                                        </ul>
                                                    </li>
                                                    <li class='has-sub'>
                                                        <a href='<?php echo BRAND_URL; ?>usergroups/viewall'>Groups</a>
                                                        <ul>
                                                            <li><a href='<?php echo BRAND_URL; ?>usergroups/create'>Add Group</a></li>
                                                        </ul>
                                                    </li>
                                                </ul>
                                            </li>

                                            <li class='has-sub'>
                                                <a href='<?php echo BRAND_URL; ?>pages/viewall'>Pages</a>
                                                <ul>
                                                    <li class='has-sub'>
                                                        <a href='<?php echo BRAND_URL; ?>pages/viewall'>Pages</a>
                                                        <ul>
                                                            <li><a href='<?php echo BRAND_URL; ?>pages/create'>Add Page</a></li>
                                                        </ul>
                                                    </li>
                                                </ul>
                                            </li>

                                            <li class='has-sub'>
                                                <a href='<?php echo BRAND_URL; ?>articles/viewall'>Articles</a>
                                                <ul>
                                                    <li class='has-sub'>
                                                        <a href='<?php echo BRAND_URL; ?>articles/viewall'>Articles</a>
                                                        <ul>
                                                            <li><a href='<?php echo BRAND_URL; ?>articles/create'>Add Article</a></li>
                                                        </ul>
                                                    </li>
                                                </ul>
                                            </li>

                                            <li><a href='<?php echo BRAND_URL; ?>comments/viewall'>Comments</a></li>
                                        </ul>
                                </li>
<?php
}

// Default Navigation
?>
                            <li class="active"><a href="/" target="_parent" title="Home">Home</a></li>
                            
<?php
if (!empty($_SESSION['user_logged_in']))
{
    // Authenticated User Navigation
?>
                            <li class="last"><a href="/users/logout" target="_parent" title="Log Out">Log Out</a></li>
<?php
} else {
    // Default Navigation
?>
                            <li class="last"><a href="/users/login" target="_parent" title="Log In">Log In</a></li>
<?php
}
?>

                            </ul>
                        </div>

                    </nav>
                </div>
            </header>
<?php
// Menu JS
$pageJavaScript[] = "
    $('#cssmenu').prepend('<div id=\"menu-button\">Menu</div>');
        $('#cssmenu #menu-button').on('click', function(){
            var menu = $(this).next('ul');
            if (menu.hasClass('open')) {
                menu.removeClass('open');
            }
            else {
                menu.addClass('open');
            }
        });";