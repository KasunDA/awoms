
<div class="footer-container">
    <footer>

        <ul class="menu_footer" id="footer_menu_left">
            <li class="heading">Store Info</li>
            <li>
                <a href="/about">About Us</a>
            </li>
            <li>
                <a href="/buy-sell-clothing">Buy &amp; Sell Clothing</a>
            </li>
            <li>
                <a href="/franchises">Franchises</a>
            </li>
            <li>
                <a href="">Store Locator</a>
            </li>
            <li>
                <a href="/owners">Franchise Login</a>
            </li>
            <li>
                <a href="">Fundraising</a>
            </li>
        </ul>

        <ul class="menu_footer" id="footer_menu_middle">
            <li class="heading">Site Info</li>
            <li>
                <a href="">Employment</a>
            </li>
            <li>
                <a href="">Terms &amp; Privacy</a>
            </li>
            <li>
                <a href="">Site Map</a>
            </li>
            <li>
                <a href="">Contact Us</a>
            </li>
        </ul>

        <ul class="menu_footer menu_footer_wood" id="footer_menu_right">
            <li>
                <a href="">Now buying &amp; selling</a>
            </li>
            <li>
                <a href="">Department 3B</a>
            </li>
            <li>
                <a href="">Department 3C</a>
            </li>
            <li>
                <a href="">Outerwear</a>
            </li>

            <li>
                <a href="">Department 3E</a>
            </li>
            <li>
                <a href="">Department 3F</a>
            </li>
            <li>
                <a href="">Dept 3G</a>
            </li>
            <li>
                <a href="">Department 3H</a>
            </li>

            <li>
                <a href="">Tank Tops</a>
            </li>
            <li>
                <a href="">Department 3J</a>
            </li>
            <li>
                <a href="">Department 3K</a>
            </li>
            <li>
                <a href="">&amp; More...</a>
            </li>
        </ul>

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