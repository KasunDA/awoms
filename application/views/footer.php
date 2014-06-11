            </div> <!-- #main-container -->

<?php
    // Home page has different middle (doesnt have top to hang over wowslider images)
    if ($_SESSION['controller'] == 'home'
            && $_SESSION['action'] == 'home')
        { $class = "middle"; } else { $class = "middle-small"; }
?>
            <!-- Middle/Bottom border -->
            <div class="<?php echo $class; ?>"></div>
            
<?php
    // Load dynamic footer nav, custom first and stop when found
    $fileLocations = Utility::getTemplateFileLocations('footer_nav');
    foreach ($fileLocations as $fileLoc){include($fileLoc);}
?>
        
        </div> <!-- #wrapper -->

        <!-- jQuery -->
<?php
        if (DEVELOPMENT_ENVIRONMENT) { echo "\n<script src='/js/libs/jquery/1.11.1/jquery.min.js'></script>"; }
        else { echo "\n<script src='//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js'></script>"; }
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
        <!-- @TODO Move to include only when needed -->
        <script>
        // Prevent jQuery UI dialog from blocking focusin
        $(document).on('focusin', function(e) {
            if ($(event.target).closest(".mce-window").length) {
                e.stopImmediatePropagation();
            }
        });
        </script>
        
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
                toolbar2: "link unlink anchor | image media responsivefilemanager | forecolor backcolor emoticons | print preview code | fullscreen",
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
          if (preg_match('/^<script/', $js)) { continue; }
        echo $js;
      }
    } else {
      echo $pageJavaScript;
    }
    echo "
        });
      </script>
      ";
    
    // Load external <script src='.... references
    foreach ($pageJavaScript as $js) {
        if (!preg_match('/^<script/', $js)) { continue; }
        echo $js;
    }
}

// Load Google Analytics (if exists)
$fileLocations = Utility::getTemplateFileLocations('googleAnalytics');
foreach ($fileLocations as $fileLoc){include($fileLoc);}

?>

    </body>
</html>