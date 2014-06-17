
<div class="footer-container">
    <footer>

<?php
        // Get dynamic menu
        $Menu = new Menu();
        $Menus['footer_store_info'] = $Menu->getMenu("Hut Store Info Menu", "menu_footer footer_menu_left", "Store Info");
        $Menus['footer_site_info'] = $Menu->getMenu("Hut Site Info Menu", "menu_footer footer_menu_middle", "Site Info");
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