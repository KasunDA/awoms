<header>

    <?php
    // Home page has different header (doesnt have bottom to hang over wowslider images)
    #if ($_SESSION['controller'] == 'home' && $_SESSION['action'] == 'home') {
    if ($_SESSION['controller'] == 'pages'
            && empty($page['pageShowTitle']))
    {
        $class = "header";
    } else {
        $class = "header-full";
    }
    ?>
    <div class="<?php echo $class; ?>">
        <nav>

            <?php
            /* Dynamic Menu: heading_nav */
            $finalMenu = NULL;
            if (!empty($_SESSION['user_logged_in'])
                    && $_SESSION['user']['usergroup']['usergroupName'] == "Administrators")
            {
                // Admins need special menu JS and div id

                $finalMenu = "<div id='cssmenu'>" . $Menus['heading_nav'] . "</div>";
                // JS required for Admin menu
                $pageJavaScript[] = "
                    $('#cssmenu').prepend('<div id=\"menu-button\">Menu</div>');
                        $('#cssmenu #menu-button').on('click', function(){
                            var menu = $(this).next('ul');
                            if (menu.hasClass('open')) { menu.removeClass('open'); }
                            else { menu.addClass('open'); }
                        });";
            }
            
            if (empty($finalMenu))
            {
                // Not logged in or no custom menu

                $finalMenu = "<div id='usermenu'>" . $Menus['heading_nav'] . "</div>";
            }
            echo $finalMenu;
            ?>

        </nav>
    </div>

</header>
