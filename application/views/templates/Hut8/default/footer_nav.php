
<div class="footer-container">
    <footer>

<?php
    // Get dynamic menu
    $Menu = new Menu();
    $Menus['footer_left'] = '';
    $Menus['footer_middle'] = '';
    $Menus['footer_right'] = '';
    // Non-logged in user menus
    if (empty($_SESSION['user_logged_in']) || empty($_SESSION['user']))
    {
        $Menus['footer_left'] = $Menu->getMenu("Hut Store Info Menu", "menu_footer footer_menu_left", "Store Info");
        $Menus['footer_middle'] = $Menu->getMenu("Hut Site Info Menu", "menu_footer footer_menu_middle", "Site Info");
        $Menus['footer_right'] = $Menu->getMenu("Hut Departments Menu", "menu_footer menu_footer_wood footer_menu_right");
    }
    // Store Owners menus
    elseif (!empty($_SESSION['user_logged_in']) && $_SESSION['user']['usergroup']['usergroupName'] == "Store Owners")
    {
        $Menus['footer_left'] = $Menu->getMenu("Owners Bottom Left Menu", "menu_footer footer_menu_left", "Store Owners");
        #$Menus['footer_middle'] = $Menu->getMenu("Hut Site Info Menu", "menu_footer footer_menu_middle", "Site Info");
        #$Menus['footer_right'] = $Menu->getMenu("Hut Departments Menu", "menu_footer menu_footer_wood footer_menu_right");
    }

        echo $Menus['footer_left'];
        echo $Menus['footer_middle'];
        echo $Menus['footer_right'];
?>
        <div id="copyright">
            &copy Copyright <?=date("Y").' '.BRAND;?> <a href='mailto:info@<?=BRAND_DOMAIN;?>'>info@<?=BRAND_DOMAIN;?></a><br />
            <!-- <cite><small>Powered by <?=ProductName.' '.Version;?></small></cite> -->
        </div>
    </footer>
</div>

<?php
// Page's JS references
if ($this->controller == "pages")
{
    if (!empty($page['pageJavaScript']))
    {
        if (!is_array($page['pageJavaScript']))
        {
            $pageJavaScript[] = html_entity_decode($page['pageJavaScript'], ENT_QUOTES);
        }
        else
        {
            foreach ($page['pageJavaScript'] as $pageJS)
            {
                $pageJavaScript[] = html_entity_decode($pageJS, ENT_QUOTES);
            }
        }
    }
}
?>

<!-- Facebook Like Counter
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
 -->