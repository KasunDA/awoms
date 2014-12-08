
<div class="footer-container">
    <footer>

        <?php
        // $Menus constructed in controller.class__construct
        echo $Menus['footer_left'];
        echo $Menus['footer_middle'];
        echo $Menus['footer_right'];
        ?>
        <div id="copyright">
            &copy Copyright <?= date("Y") . ' ' . $_SESSION['brand']['brandName']; ?>
            <a href='mailto:<?php echo $_SESSION['brand']['brandEmail']; ?>'>
                <?php echo $_SESSION['brand']['brandEmail']; ?>
            </a><br />
            <!-- <cite><small>Powered by <?= ProductName . ' ' . Version; ?></small></cite> -->
        </div>
    </footer>
</div>

<?php
// Page's JS references
if ($this->controller == "pages") {
    if (!empty($page['pageJavaScript'])) {
        if (!is_array($page['pageJavaScript'])) {
            $pageJavaScript[] = html_entity_decode($page['pageJavaScript'], ENT_QUOTES);
        } else {
            foreach ($page['pageJavaScript'] as $pageJS) {
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