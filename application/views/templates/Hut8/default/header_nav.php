            <header>
                
<?php
    // Home page has different header (doesnt have bottom to hang over wowslider images)
    if ($_SESSION['action'] == 'home') { $class = "header"; } else { $class = "header-full"; }
?>
                <div class="<?php echo $class; ?>">
                    <nav>
                        <ul class="menu_horizontal menu_header menu_hover">

<?php
    if (!empty($_SESSION['user_logged_in']))
    {
?>
                            <li><a href="/" target="_parent" title="Home">Home</a></li>
                            <li><a href="/" target="_parent" title="Owners Home">Owners Home</a></li>
                            <li><a href="/" target="_parent" title="Store Info">Store Info</a></li>
                            <li><a href="/" target="_parent" title="Owners Store">Owners Store</a></li>
                            <li><a href="/users/logout" title="Log Out">Log Out</a></li>
<?php
    }
    else
    {
        // Default Navigation
?>
                            <li><a href="/" target="_parent" title="Home">Home</a></li>
                            <li><a href="/about" target="_parent" title="About Hut no. 8">About Us</a></li>
                            <li><a href="/buy-sell-clothing" target="_parent" title="Buy &amp; Sell Clothing">Buy &amp; Sell Clothing</a></li>
                            <li><a href="/" target="_parent" title="Franchises">Franchises</a></li>
                            <li><a href="/" target="_parent" title="Store Locator">Store Locator</a></li>
<?php
    }
?>

                        </ul>
                    </nav>
                </div>
                
            </header>
