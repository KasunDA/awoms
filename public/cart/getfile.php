<?php

//
// Example getfile usage:
//
// /getfile.php?cartID=1&imageID=1&w=128&h=128

/***** BEGIN CART CODE *****/
/* ! IMPORTANT: DO NOT CHANGE THE BEGINNING OR ENDING TAGS OF THIS 'CART CODE' SECTION ! */
/*****
 * Set $cartID to your cart ID
 * Set $cartPrivateSettingsFile to the FULL PATH to the 'cart_settings.inc.php' file
 * Note: This file should NOT be in the public web root for security
 * Example: "/var/www/vhosts/cart.com/private/cart/cart_settings.inc.php"
 * Example: "C:\wamp\www\cart.com\private\cart\cart_settings.inc.php";
 *****/
//$cartPrivateSettingsFile = "E:/Projects/GPFC/cart/cart_settings.inc.php";
$cartPrivateSettingsFile = "../../kcart/cart_settings.inc.php";
// This makes available: $Brand/$brand, $Store/$store, $Cart/$cart
require_once($cartPrivateSettingsFile);
\Errors::debugLogger(PHP_EOL . '***** New GetFile Load (' . $_SERVER['REQUEST_URI'] . ') *****', 10, true);
\Errors::debugLogger(PHP_EOL . serialize($_GET) . PHP_EOL . '*****' . PHP_EOL, 10);

// Verify & Sanitize Request
$s    = new killerCart\Sanitize();
$args = array('cartID'    => FILTER_VALIDATE_INT,
    'imageID' => FILTER_VALIDATE_INT,
    'w'  => FILTER_VALIDATE_INT,
    'h'  => FILTER_VALIDATE_INT);
$san  = $s->filterArray(INPUT_GET, $args);
$validated = TRUE;
if (!$san) {
    $validated = FALSE;
}
foreach ($san as $s) { // All fields required
    if (empty($s)) {
        $validated = FALSE;
        break;
    }
}
if ($validated === FALSE) {
    \Errors::debugLogger(__FILE__ . ':' . __LINE__ . ': Invalid GET ' . serialize($san));
    // Log error (if not in dev mode)
    if (OPMODE != 'DEV') {
        trigger_error('Invalid GET: '.serialize($san), E_USER_NOTICE);
    }
    return false;
}

// Image Class
$image = new killerCart\Image();

// Image Info
$img = $image->getImageInfoByID($san['imageID']);

if (empty($img)) {
    $fileExists = FALSE;
} else {
    
    if (!empty($img['parentImageID'])) {
        $imgPathID = $img['parentImageID'];
    } else {
        $imgPathID = $san['imageID'];
    }
    
    $filePath = cartImagesDir.$img['cartID'].'/'.$imgPathID.'/'.$imgPathID.'.'.$san['w'].'x'.$san['h'].'.'.$img['imageExt'];
    if (!is_file($filePath)) {
        $fileExists = FALSE;
    } else {
        $fileExists = TRUE;
    }
}
if ($fileExists === FALSE) {
    header('Content-Type: image/png');
    // Default holder.js if no image found
    if ((int)$san['w'] == '256') {
        echo base64_decode("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");
    }
    elseif ((int)$san['w'] <= '128') {
        echo base64_decode("iVBORw0KGgoAAAANSUhEUgAAAIAAAACACAYAAADDPmHLAAAEE0lEQVR4Xu2aPUskQRRFawIFFQPBr1RMNRFE8e8bqSBmYiymY6Qg+LFbDSW9szPurPQVr/dMtGyPr16/c7r6vWJG4/H4rfCJrcAIAWLZdzeOANn8ESCcPwIgAE1gtAP0ANH4aQLD8SMAAnAOkO0APUA2f8bAcP4IgACcA0Q7QA8QjZ8xMBw/AiAA5wDZDtADZPNnDAznjwAIwDlAtAP0ANH4GQPD8SMAAnAOkO0APUA2f8bAcP4IgACcA0Q7QA8QjZ8xMBw/AiAA5wDZDtADZPNnDAznjwAIwDlAtAP0ANH4GQPD8SMAAnAOkO0APUA2f8bAcP4IgACcA0Q7QA8QjZ8xMBw/AiAA5wDZDtADZPNnDAznjwAIwDlAtAP0ANH4GQPD8SMAAnAOkO0APUA2f58x8PX1tZydnZXHx8dycnJSlpaWOnS3t7fl5ubmHePOzk7Z3d2dem1vb69sb2/Phfyr15srKcGXLHaA5+fncnp6Wl5eXspoNHoX4Pfrq1xdXZWNjY2yv79frq+vy93dXamg19fXu79ZXl4uR0dH5fLystzf3/8hz6x6fvV6Aq5zh/z2AvRh1LvqCzB5l203qLvA2traTDkWFxe7aysrK50c08Spsg213ry7ztzUBvyihQAXFxfl4OCge4onXwH9WvzPDtB2hOPj43J+fv6+U1ThFOsNyGzQUN9egHa3s97J7Xp7+ldXV7unun5mbeX/ulavD73eoNQGDPYjBGi9QN3aK/yFhYXyUX/QtuS6C9TvbW1tdT1E//ORAJ9db0Bug4WyF6A95RV6g1+r014HbSro9wd1SmgQ6989PT11jWP/XT1LgM+uNxixgQNZC9AgPTw8/AXwox1gc3OzGylro3d4eNj1FvXffYGmCfDZ9WgCB7B2GpDJM4C2zORTP/n/s3aH/qtgyPUGuH1ZCJsdQFaB8MAIgADjt/AaRN8+O0A0fn4PEI4fARCAH4RkO0APkM3f5wch4Zxkt88OICutR2AE8OAkyxIBZKX1CIwAHpxkWSKArLQegRHAg5MsSwSQldYjMAJ4cJJliQCy0noERgAPTrIsEUBWWo/ACODBSZYlAshK6xEYATw4ybJEAFlpPQIjgAcnWZYIICutR2AE8OAkyxIBZKX1CIwAHpxkWSKArLQegRHAg5MsSwSQldYjMAJ4cJJliQCy0noERgAPTrIsEUBWWo/ACODBSZYlAshK6xEYATw4ybJEAFlpPQIjgAcnWZYIICutR2AE8OAkyxIBZKX1CIwAHpxkWSKArLQegRHAg5MsSwSQldYjMAJ4cJJliQCy0noERgAPTrIsEUBWWo/ACODBSZYlAshK6xEYATw4ybJEAFlpPQIjgAcnWZYIICutR2AE8OAkyxIBZKX1CPwL46ryLnS9BZwAAAAASUVORK5CYII=");
    }
    // Log error (if not in dev mode)
    if (OPMODE != 'DEV') {
        trigger_error('Image does not exist: '.$san['imageID'], E_USER_NOTICE);
    }
    return false;
}

// Display Image
$image->displayImage($filePath);