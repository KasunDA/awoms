<header>

    <?php
    // Home page has different header (doesnt have bottom to hang over wowslider images)
    if ($_SESSION['controller'] == 'home' && $_SESSION['action'] == 'home') {
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
            $Menus['header_nav'] = $Menu->getMenu("Hut Top Menu", "menu_horizontal menu_header menu_hover");
            if (!empty($_SESSION['user_logged_in'])) {
                $finalMenu        = "<div id='cssmenu'>
                        " . $Menus['header_nav'] . "
                      </div>";
                // Menu JS (admin)
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
            } else {
                $finalMenu = "<div id='usermenu'>
                        " . $Menus['header_nav'] . "
                      </div>";
            }
            echo $finalMenu;
            ?>

        </nav>
    </div>

</header>
