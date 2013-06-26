<?php
// Articles :: Write Modal
include(ROOT.DS.'application'.DS.'views'.DS.'articles'.DS.'writeForm.php');
$pageJavaScript[] = "
  // Open Modal Cursor
  $('.openModal').css('cursor', 'pointer');
";
?>

          </div> <!-- #main -->
        </div> <!-- #main-container -->

        <div class="footer-container">
            <footer class="wrapper">
                <h3><?=BRAND;?>.com</h3>
                <cite>contact: info@<?=BRAND;?>.com</cite>
            </footer>
        </div>

        <!-- jQuery -->
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src="/js/libs/jquery/1.9.1/jquery.min.js"><\/script>')</script>
        <!-- jQuery UI -->
        <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>

<?php
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
?>

<?php
// Analytics
if (file_exists('googleAnalytics.php')) {
  include('googleAnalytics.php');
}
?>

    </body>
</html>