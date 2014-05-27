
          </div> <!-- #main -->
        </div> <!-- #main-container -->

        <div class="footer-container">
            <footer class="wrapper">
                <h3><?=BRAND;?>.com</h3>
                <cite>contact: info@<?=BRAND;?>.com</cite>
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