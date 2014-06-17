
<div class="footer-container">
    <footer>

<?php
        // Get dynamic menu
        $Menu = new Menu();
        $Menus['footer_store_info'] = $Menu->getMenu("Hut Store Info Menu", "menu_footer footer_menu_left");
        $Menus['footer_site_info'] = $Menu->getMenu("Hut Site Info Menu", "menu_footer footer_menu_middle");
        $Menus['footer_departments'] = $Menu->getMenu("Hut Departments Menu", "menu_footer menu_footer_wood footer_menu_right");
        
        echo $Menus['footer_store_info'];
        echo $Menus['footer_site_info'];
        echo $Menus['footer_departments'];
?>
        <div id="copyright">
            &copy Copyright <?=date("Y").' '.BRAND;?> <a href='mailto:info@<?=BRAND_DOMAIN;?>'>info@<?=BRAND_DOMAIN;?></a><br />
            <!-- <cite><small>Powered by <?=ProductName.' '.Version;?></small></cite> -->
        </div>
    </footer>
</div>

<?php
// Only include Wowslider on front page
if ($this->controller == "home")
{
    $pageJavaScript[] = "<script src='/css/Hut8/old/wowslider/wowslider.js'></script>";
    $pageJavaScript[] = "<script src='/css/Hut8/old/wowslider/script.js'></script>";
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