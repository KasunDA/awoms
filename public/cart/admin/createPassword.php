<?php

ini_set('display_errors', 'On');
ini_set('display_startup_errors', 'On');
ini_set('html_errors', 'On');
error_reporting(-1);
require ('../../../private/cart/cart_settings.inc.php');
$user       = new killerCart\User();
// Set desired passphrase here, copy the output to the database users table to reset your password manually
$passphrase = 'test1234';
$hash       = $user->getPassphraseHash($passphrase);
echo $hash;
?>
