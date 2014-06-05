
            </div> <!-- #main-container -->

<?php
    // Home page has different middle (doesnt have top to hang over wowslider images)
    if ($this->action == 'home') {
        $class = "middle";
    } else {
        $class = "middle-small";
    }
?>

            <!-- Middle/Bottom border -->
            <div class="<?php echo $class; ?>"></div>
            
            <div class="footer-container">
                <footer>
                    
                    <ul class="menu_footer" id="footer_menu_left">
                        <li class="heading">Store Info</li>
                        <li>
                            <a href="">About Us</a>
                        </li>
                        <li>
                            <a href="">Buy &amp; Sell Clothing</a>
                        </li>
                        <li>
                            <a href="">Franchises</a>
                        </li>
                        <li>
                            <a href="">Store Locator</a>
                        </li>
                        <li>
                            <a href="/users/login">Franchise Login</a>
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
        
        </div> <!-- #wrapper -->        
        

        <!-- jQuery -->
<?php
        if (DEVELOPMENT_ENVIRONMENT)
        {
            echo "<script src='/js/libs/jquery/1.11.1/jquery.min.js'></script>";
        }
        else
        {
            echo "<script src='//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js'></script>";
        }
?>
        <script>window.jQuery || document.write('<script src="/js/libs/jquery/1.11.1/jquery.min.js"><\/script>')</script>

        <!-- jQuery UI -->
        <script src='/js/libs/jqueryui/1.10.4/jquery-ui.min.js'></script>

<?php
        // Only include if logged in
        if (!empty($_SESSION['user_logged_in']))
        {
?>
        <!-- WYSIWYG -->
        <script type="text/javascript" src="/js/libs/tinymce/tinymce.min.js"></script>
        <script type="text/javascript">
            tinymce.init({
                selector: ".tinymce",
                theme: "modern",
                width: 680,
                height: 300,
                plugins: [
                    "advlist autolink lists link image charmap print preview hr anchor pagebreak",
                    "searchreplace wordcount visualblocks visualchars code fullscreen",
                    "insertdatetime media nonbreaking save table contextmenu directionality",
                    "emoticons template paste textcolor spellchecker responsivefilemanager"
                ],
                toolbar1: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent",
                toolbar2: "link unlink anchor | image media responsivefilemanager | forecolor backcolor emoticons | print preview code",
                image_advtab: true,
                external_filemanager_path:"/filemanager/",
                filemanager_title:"Filemanager",
                filemanager_access_key:"DefStoAxeKey",
                external_plugins: { "filemanager" : "/filemanager/plugin.min.js" }
            });
        </script>
<?php
        }

// GLOBAL: API Action
 include(ROOT.DS."application".DS."views".DS."API".DS."API_include.php");

// GLOBAL: API JS
 if (!empty($pageJavaScript))
 {
    array_unshift($pageJavaScript, file_get_contents(ROOT.DS."application".DS."views".DS."API".DS."API.js"));
 } else {
    $pageJavaScript[] = file_get_contents(ROOT.DS."application".DS."views".DS."API".DS."API.js");
 }

// GLOBAL: Form Cursor
$pageJavaScript[] = "
  // Open Modal Cursor
  $('.openModal').css('cursor', 'pointer');
";

// GLOBAL: Load Page JS
if (!empty($pageJavaScript)) {
    echo "
      <script type='text/javascript'>
        $(document).ready(function() {
        ";
    
    if (is_array($pageJavaScript)) {
      foreach ($pageJavaScript as $js) {
        echo $js;
      }
    } else {
      echo $pageJavaScript;
    }
    echo "
        });
      </script>
      ";
}

// Analytics
if (file_exists('googleAnalytics.php')) {
  include('googleAnalytics.php');
}

/******************
 * BRAND SPECIFIC JS
 ******************/

// Only include on front page
if ($this->controller == "home")
{
?>
    <!-- Wow slider -->
    <script src='/css/Hut8/old/wowslider/wowslider.js'></script>
    <script src='/css/Hut8/old/wowslider/script.js'></script>
<?php
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
    </body>
</html>