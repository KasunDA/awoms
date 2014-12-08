</div> <!-- #main-container -->

<?php
// Home page has different middle (doesnt have top to hang over wowslider images)
#if ($_SESSION['controller'] == 'home'
#&& $_SESSION['action'] == 'home')
#{ $class = "middle"; } else { $class = "middle-small"; }
if ($_SESSION['controller'] == 'pages' && empty($page['pageShowTitle']))
{
    $class = "middle";
}
else
{
    $class = "middle-small";
}
?>
<!-- Middle/Bottom border -->
<div class="<?php echo $class; ?>"></div>

<?php
// Load dynamic footer nav, custom first and stop when found
$fileLocations = Utility::getTemplateFileLocations('footer_nav');
foreach ($fileLocations as $fileLoc)
{
    include($fileLoc);
}
?>

</div> <!-- #wrapper -->

<!-- jQuery -->
<?php
if (DEVELOPMENT_ENVIRONMENT)
{
    echo "\n<script src='/js/libs/jquery/2.1.1/jquery.min.js'></script>";
}
else
{
    echo "\n<script src='//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js'></script>";
    echo "\n<script>window.jQuery || document.write('<script src=\"/js/libs/jquery/2.1.1/jquery.min.js\"><\/script>')</script>";
}
?>

<!--
jQuery UI
<script src='/js/libs/jqueryui/1.10.4/jquery-ui.min.js'></script>
-->

<?php
// Only include if logged in
if (!empty($_SESSION['user_logged_in']))
{
    ?>
    <!-- WYSIWYG -->
    <!--
    /**
     * For use with jQueryUI:
     *
    @TODO Move to include only when needed
    <script>
        // Prevent jQuery UI dialog from blocking focusin
        $(document).on('focusin', function(e) {
            if ($(event.target).closest(".mce-window").length) {
                e.stopImmediatePropagation();
            }
        });
    </script>
    -->


    <!--
    /**
     * jQuery File Upload
     **/
    -->

    <!-- The jQuery UI widget factory, can be omitted if jQuery UI is already included -->
    <script src="/js/libs/jqueryui/1.10.4/jquery.ui.widget.js"></script>

    <!-- The Load Image plugin is included for the preview images and image resizing functionality -->
    <!-- <script src="http://blueimp.github.io/JavaScript-Load-Image/js/load-image.min.js"></script> -->
    <script src="/js/libs/js-load-image/load-image.all.min.js"></script>
    
    <!-- The Canvas to Blob plugin is included for image resizing functionality -->
    <!-- <script src="http://blueimp.github.io/JavaScript-Canvas-to-Blob/js/canvas-to-blob.min.js"></script> -->
    <script src="/js/libs/js-load-image/canvas-to-blob.min.js"></script>

    <!-- Bootstrap JS is not required, but included for the responsive demo navigation
    <script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
    -->

    <!-- The Iframe Transport is required for browsers without support for XHR file uploads -->
    <script src="/js/libs/jquery-file-upload/jquery.iframe-transport.js"></script>
    <!-- The basic File Upload plugin -->
    <script src="/js/libs/jquery-file-upload/jquery.fileupload.js"></script>
    <!-- The File Upload processing plugin -->
    <script src="/js/libs/jquery-file-upload/jquery.fileupload-process.js"></script>
    <!-- The File Upload image preview & resize plugin -->
    <script src="/js/libs/jquery-file-upload/jquery.fileupload-image.js"></script>
    <!-- The File Upload audio preview plugin -->
    <script src="/js/libs/jquery-file-upload/jquery.fileupload-audio.js"></script>
    <!-- The File Upload video preview plugin -->
    <script src="/js/libs/jquery-file-upload/jquery.fileupload-video.js"></script>
    <!-- The File Upload validation plugin -->
    <script src="/js/libs/jquery-file-upload/jquery.fileupload-validate.js"></script>

    <!-- FancyBox -->

    <!-- Add mousewheel plugin (this is optional)
    <script type="text/javascript" src="/js/libs/fancybox/jquery.mousewheel-3.0.6.pack.js"></script>
    -->

    <!-- Add fancyBox main JS and CSS files -->
    <script type="text/javascript" src="/js/libs/fancybox/jquery.fancybox.js?v=2.1.5"></script>
    <link rel="stylesheet" type="text/css" href="/js/libs/fancybox/jquery.fancybox.css?v=2.1.5" media="screen" />

    <!-- Add Button helper (this is optional) -->
    <link rel="stylesheet" type="text/css" href="/js/libs/fancybox/helpers/jquery.fancybox-buttons.css?v=1.0.5" />
    <script type="text/javascript" src="/js/libs/fancybox/helpers/jquery.fancybox-buttons.js?v=1.0.5"></script>

    <!-- Add Thumbnail helper (this is optional) -->
    <link rel="stylesheet" type="text/css" href="/js/libs/fancybox/helpers/jquery.fancybox-thumbs.css?v=1.0.7" />
    <script type="text/javascript" src="/js/libs/fancybox/helpers/jquery.fancybox-thumbs.js?v=1.0.7"></script>

    <!-- Add Media helper (this is optional) -->
    <script type="text/javascript" src="/js/libs/fancybox/helpers/jquery.fancybox-media.js?v=1.0.6"></script>


    <!-- TinyMCE -->
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
            external_filemanager_path: "/filemanager/",
            filemanager_title: "Filemanager",
            filemanager_access_key: "DefStoAxeKey",
            external_plugins: {"filemanager": "/filemanager/plugin.min.js"}
        });
    </script>
    <?php
}


/**
 * For use with jQueryUI:
 *
  // GLOBAL: API Action
  if (empty($_SESSION['template']) && !empty($formID)) {
  include(ROOT . DS . "application" . DS . "views" . DS . "API" . DS . "API_include.php");
  }
 * 
 */

// GLOBAL: API JS
if (!empty($pageJavaScript))
{
    array_unshift($pageJavaScript, file_get_contents(ROOT . DS . "application" . DS . "views" . DS . "API" . DS . "API.js"));
}
else
{
    $pageJavaScript[] = file_get_contents(ROOT . DS . "application" . DS . "views" . DS . "API" . DS . "API.js");
}

// GLOBAL: Form Cursor
$pageJavaScript[] = "
  // Open Modal Cursor
  $('.openModal').css('cursor', 'pointer');
";

// GLOBAL: Load Page JS
if (!empty($pageJavaScript))
{
    echo "
      <script type='text/javascript'>
        $(document).ready(function() {
        ";

    if (is_array($pageJavaScript))
    {
        foreach ($pageJavaScript as $js)
        {
            if (preg_match('/^<script/', $js))
            {
                continue;
            }
            echo $js;
        }
    }
    else
    {
        echo $pageJavaScript;
    }
    echo "
        });
      </script>
      ";

    // Load external <script src='.... references
    foreach ($pageJavaScript as $js)
    {
        if (!preg_match('/^<script/', $js))
        {
            continue;
        }
        echo $js;
    }
}

// Load Google Analytics (if exists)
$fileLocations = Utility::getTemplateFileLocations('googleAnalytics');
foreach ($fileLocations as $fileLoc)
{
    include($fileLoc);
}
?>

</body>
</html>