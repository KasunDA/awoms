            <header>
                
<?php
    // Home page has different header (doesnt have bottom to hang over wowslider images)
    if ($_SESSION['controller'] == 'home'
            && $_SESSION['action'] == 'home')
        { $class = "header"; } else { $class = "header-full"; }
?>
                <div class="<?php echo $class; ?>">
                    <nav>
                        
<?php
    if (!empty($_SESSION['user_logged_in']))
    {
        $Menu = new MenusController('menus', 'Menu', 'admin', 'm');
        $finalMenu = $Menu->admin();
        $finalMenu = "<div id='cssmenu'>
                        ".$finalMenu."
                      </div>";
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
    }
    else
    {
        $Menu = new MenusController('menus', 'Menu', 'admin', 'm');
        $finalMenu = $Menu->user();
        $finalMenu = "<div id='usermenu'>
                        ".$finalMenu."
                      </div>";
    }
    
    echo $finalMenu;
?>
                        
                    </nav>
                </div>
                
            </header>
