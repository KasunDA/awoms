<h1>Generic Home Template (<?php echo __FILE__; ?>)</h1>

<?php
return;


/* * *** BEGIN CART CODE **** */
/* ! IMPORTANT: DO NOT CHANGE THE BEGINNING OR ENDING TAGS OF THIS 'CART CODE' SECTION ! */
/* * ***
 * Set $cartID to your cart ID
 * Set $cartPrivateSettingsFile to the FULL PATH to the 'cart_settings.inc.php' file
 * Note: This file should NOT be in the public web root for security
 * Example: "/var/www/vhosts/cart.com/private/cart/cart_settings.inc.php"
 * Example: "C:\wamp\www\cart.com\private\cart\cart_settings.inc.php";
 * *** */
$cartPrivateSettingsFile = "E:/Projects/GPFC/cart/cart_settings.inc.php";
require_once($cartPrivateSettingsFile);
\Errors::debugLogger(PHP_EOL . '***** New Page Load (' . $_SERVER['REQUEST_URI'] . ') *****', 1, true);
\Errors::debugLogger(PHP_EOL . serialize($_POST) . PHP_EOL . '*****' . PHP_EOL, 8);
// Load cart class and session data
$cart                    = new killerCart\Cart(CART_ID);
// Authentication and Authorization
$auth                    = new killerCart\Auth();
$sessionName = cartCodeNamespace . 'Customer';
// Start Session
$auth->startSession($sessionName);
// Session cart info
if (empty($_SESSION['cartID'])) {
    $cart->getCartInfo(CART_ID);
    $_SESSION['cartID']    = $cart->session['cartID'];
    $_SESSION['cartName']  = $cart->session['cartName'];
    $_SESSION['cartTheme'] = $cart->session['cartTheme'];
}
// Stop loading rest if in ajax mode or mini-view mode
if ((!empty($_REQUEST['m']) && $_REQUEST['m'] == 'ajax') || (!empty($isCartMini))) {return;}
/***** END CART CODE *****/

$image = new killerCart\Image();
$s = $cart->getCartInfo($_SESSION['cartID']);
$car = $cart->getStorefrontCarousel($_SESSION['cartID']);

if (empty($car))
{
    include(ROOT.DS.'cart'.DS.'templates'.DS.'default'.DS.'storefront_home.inc.phtml'); //product_category'.DS.'product_category_list.inc.phtml');
    return;
}

$slideCount = 0;
$tallestHeight = 0;
foreach ($car as $k => $v)
{
  if ($k == "cartID" || $k == "interval" || $v == NULL)
  {
    continue;
  }
  $slideCount++;
}
$slides = array();
$visibleSlides = 0;
if ($s['storefrontCarousel'] && $slideCount > 0) {
  $count = 1;
  $slide = array();
  foreach ($car as $k => $v)
  {
    if ($k == "cartID" || $k == "interval")
    {
      continue;
    }
    
    if (preg_match('/ImageID/', $k) && $v != NULL)
    {
        $img = $image->getImageInfoByID($v);
        $bi = $image->getBestFitImage( $cartID, $v, $img['imageWidth'], $img['imageHeight'], '768', '768');
        if ($bi['bestHeight'] > $tallestHeight) { $tallestHeight = $bi['bestHeight']; }
        $visibleSlides++;
    }
    $slide[$k] = $v;
    $count++;
    if ($count > 4)
    {
      $slides[] = $slide;
      $count = 1;
      $slide = array();
    }
  }
?>
<div class='container'>
    <div class='row'>
        <div class='span12'>

            <!-- Carousel - Main images -->
            
            <!-- Set height to max image height -->
            <div id='storefrontCarousel' class='carousel slide span12' style="height: <?php echo $tallestHeight; ?>px">

<?php
if ($visibleSlides > 1)
{
?>
                <!-- Indicators (should match number of carousel items -->
                <ol class='carousel-indicators'>
<?php
$i = 1;
foreach ($slides as $sl)
{
  if (empty($sl['slide'.$i.'ImageID']))
  {
    continue; 
  }
  if ($i == 1) {$active = " class='active'";} else {$active="";}
  echo "<li data-target='#storefrontCarousel' data-slide-to='".$i."'".$active."></li>";
  $i++;
}
?>
                </ol>
<?php
}
?>
                <!-- Carousel items -->
                <div class='carousel-inner'>

<?php
$i = 1;
$tallestHeight = 0;
foreach ($slides as $sl)
{
  if (empty($sl['slide'.$i.'ImageID']))
  {
    continue; 
  }
  if ($i == 1) {$active = "active ";} else {$active="";}
  $tid = $sl['slide'.$i.'ImageID'];
  $ttit = $sl['slide'.$i.'Title'];
  $turl = $sl['slide'.$i.'URL'];
  $tdes = $sl['slide'.$i.'Description'];
  $img = $image->getImageInfoByID($tid);
  $bi = $image->getBestFitImage( $cartID, $tid, $img['imageWidth'], $img['imageHeight'], '768', '768');
  $sisrc = "/getfile.php?cartID=".$cartID."&imageID=".$tid."&w=".$bi['bestWidth']."&h=".$bi['bestHeight'];
?>
                    <div class="<?php echo $active; ?>item">
                        <div class="container">
                            <div class="row">
                                <div class="span12 text-center">
                                  <?php if (!empty($turl)) { ?>
                                    <a href="<?php echo $turl; ?>">
                                  <?php } ?>
                                        <img src="<?php echo $sisrc; ?>" alt="<?php echo $ttit; ?>">
                                  <?php if (!empty($turl)) { ?>
                                    </a>
                                  <?php } ?>
                                </div>
                                <!--
                                <div class="span4">
                                  <h2>
                                  <?php if (!empty($turl)) { ?>
                                    <a href="<?php echo $turl; ?>">
                                  <?php } ?>
                                  <?php echo $ttit; ?>
                                  <?php if (!empty($turl)) { ?>
                                    </a>
                                  <?php } ?>
                                  </h2>
                                  <?php echo $tdes; ?>
                                </div>
                                -->
                            </div>
                        </div>
                    </div>
<?php
$i++;
}
?>
                </div>

<?php
if ($visibleSlides > 1)
{
?>
                <!-- Carousel nav btns -->
                <a class='carousel-control left' href='#storefrontCarousel' data-slide='prev'>&lsaquo;</a>
                <a class='carousel-control right' href='#storefrontCarousel' data-slide='next'>&rsaquo;</a>
<?php
}
?>
            </div>
        </div>
    </div>
</div>
<?php
}
?>

<div class='clearfix'/>

<div class='container'>
    <div class="row">
        <div class="span12">
            <?php echo $s['storefrontDescription']; ?>
        </div>
    </div>
</div>

<?php
$pageJavaScript[] = "
<script>
$(document).ready(function() {
    var mic = $('#storefrontCarousel');
    mic.carousel({
        interval: ".($car['interval'] * 1000)."
    });
});
</script>";
?>