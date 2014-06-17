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
            // Get dynamic menu
            $Menu = new Menu();
            if (!empty($_SESSION['user_logged_in'])) {
                if ($_SESSION['user']['usergroup']['usergroupName'] == "Administrators")
                {
                    $Menus['header_nav'] = $Menu->getMenu("admin");
                    $finalMenu = "<div id='cssmenu'>" . $Menus['header_nav'] . "</div>";
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
                }
                elseif ($_SESSION['user']['usergroup']['usergroupName'] == "Store Owners")
                {
                    $Menus['header_nav'] = $Menu->getMenu("Owners Top Menu", "menu_horizontal menu_header menu_hover");
                    $finalMenu = "<div id='usermenu'>" . $Menus['header_nav'] . "</div>";
                }
            }
            if (empty($finalMenu))
            {
                $Menus['header_nav'] = $Menu->getMenu("Hut Top Menu", "menu_horizontal menu_header menu_hover");
                $finalMenu = "<div id='usermenu'>" . $Menus['header_nav'] . "</div>";
            }
            echo $finalMenu;
            ?>

        </nav>
    </div>

</header>
