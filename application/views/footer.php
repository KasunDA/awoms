            </div> <!-- #main -->
        </div> <!-- #main-container -->

        <div class="footer-container">
            <footer class="wrapper">
                <h3><?=BRAND;?>.com</h3>
                <cite>contact: info@<?=BRAND;?>.com</cite>
            </footer>
        </div>

        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src="/js/vendor/jquery-1.9.1.min.js"><\/script>')</script>

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

<!--
Google Analytics
        <script>
            var _gaq=[['_setAccount','UA-XXXXX-X'],['_trackPageview']];
            (function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
            g.src=('https:'==location.protocol?'//ssl':'//www')+'.google-analytics.com/ga.js';
            s.parentNode.insertBefore(g,s)}(document,'script'));
        </script>
-->
    </body>
</html>