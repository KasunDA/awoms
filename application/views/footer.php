<div id='footer'>
  Default Footer<br />
  BRAND: <?php echo BRAND; ?>
  <?php var_dump($this); ?>
</div>

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script>window.jQuery || document.write('<script src="<?php echo BRANDURL; ?>js/vendor/jquery-1.9.1.min.js"><\/script>')</script>

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