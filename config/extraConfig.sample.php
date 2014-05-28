<?php
// Set this to an email address to receive any errors triggered by the site
define('ERROR_EMAIL', 'senderrors@tohere.com');

// Set this to your preferred default time zone according to https://php.net/manual/en/timezones.php
date_default_timezone_set('America/New_York');

/***** ERROR_LEVEL
 * Set ERROR_LEVEL to the level of details to record, higher levels include all child levels (e.g. 5 includes 4,3,2,1)
 * 10 (Everything!) - Utilities(), Sanitize() - (PCI Violation!) Will cause a lot of logging *Be sure to only enable temporarily and delete logs afterwards as they contain sensitive info*
 * 9 (Sensitive!) - Auth() - (PCI Violation!) Encryption/decryption/authentication raw information *Be sure to only enable temporarily and delete logs afterwards as they contain sensitive info*
 * 8 (Form data) - func_get_args() - (PCI Violation!) All raw form/post data and function arguments *Be sure to only enable temporarily and delete logs afterwards as they contain sensitive info*
 * 7 - 
 * 6 - *Anything higher than 5 is to be considered non-PCI compliant and for temporary debugging purposes only, with log cleanup afterwards.
 * 5 (Debug) - __METHOD__ / __FILE__ / __LINE__ PCI Safe debugging level
 * 4 - 
 * 3 - 
 * 2 - 
 * 1 (Info) - If msg lvl undefined assumes level 1
 * 0 (None) - Disabled (Unless forced) - Recommended for production
 *****/
if ($_SERVER['SERVER_ADDR'] == '127.0.0.1') {
    // Localhost development settings (no need to change these)
    define('DEVELOPMENT_ENVIRONMENT', TRUE);
    define('ERROR_LEVEL', 9);
} else {
    // Production settings (set ERROR_LEVEL as desired with notes above in mind, 0 recommended for live server)
    define('DEVELOPMENT_ENVIRONMENT', FALSE);
    define('ERROR_LEVEL', 0);
}