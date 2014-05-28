
          </div> <!-- #main -->
        </div> <!-- #main-container -->

        <div class="footer-container">
            <footer class="wrapper">
                <small>Copyright &copy; <?=date("Y").' '.BRAND;?> <a href='mailto:info@<?=BRAND_DOMAIN;?>'>info@<?=BRAND_DOMAIN;?></a></small><br />
                <cite><br /><small>Powered by <?=ProductName.' '.Version;?></small></cite>
            </footer>
        </div>

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
// GLOBAL: API Action
 include(ROOT.DS."application".DS."views".DS."API".DS."API_include.php");

// GLOBAL: API JS
array_unshift($pageJavaScript, file_get_contents(ROOT.DS."application".DS."views".DS."API".DS."API.js"));

// GLOBAL: Form Cursor
$pageJavaScript[] = "
  // Open Modal Cursor
  $('.openModal').css('cursor', 'pointer');
";

// GLOBAL: Load Page JS
if (!empty($pageJavaScript)) {
    echo "
      <script type='text/javascript'>
        $(document).ready(function() {";
    if (is_array($pageJavaScript)) {
      foreach ($pageJavaScript as $js) {
        echo $js;
      }
    } else {
      echo $pageJavaScript;
    }
    echo "
        });
      </script>";
}

// Analytics
if (file_exists('googleAnalytics.php')) {
  include('googleAnalytics.php');
}
?>

    </body>
</html>