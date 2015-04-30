<?php
namespace killerCart;
use DateTime;
use DateTimeZone;
use DateInterval;

/**
 * Util class
 *
 * Utilities methods
 *
 * PHP version 5
 *
 * @category  killerCart
 * @package   killerCart
 * @author    Brock Hensley <brock@brockhensley.com>
 * @version   v0.0.1
 */
class Util
{
    /**
     * handleFileUpload
     * 
     * Handles all file uploads
     * 
     * @version v0.0.1
     * 
     * @since v0.0.1
     * 
     * @param string $htmlFilesName
     * @param string  $fileType
     * @param int $cartID
     * @param null|int $productID
     * @param null|int $categoryID
     * @param null|int $customerID
     * @param null|int $userID
     * 
     * @return boolean
     * 
     * @todo Sanitize POST
     */
    public static function handleFileUpload($htmlFilesName, $fileType, $cartID, $productID = NULL, $categoryID = NULL,
                                            $customerID = NULL, $userID = NULL)
    {
        \Errors::debugLogger(__METHOD__, 10);
        \Errors::debugLogger(func_get_args(), 10);
        //
        // File Type
        //
        if ($fileType == 'image') {
            //
            // Images
            //
            // Text Overlay, Resize

            // Init Image
            $image = new Image();

            // Define text overlay if set
            if (!empty($_POST['enableTextOverlay'])) {
                $textOverlay  = $_POST['textOverlay'];
                $fontFile     = $_POST['fontFile'];
                $fontSize     = $_POST['fontSize'];
                $fontColor    = $_POST['fontColor'];
                $fontPosition = $_POST['fontPosition'];
                $xOffset      = $_POST['xOffset'];
                $yOffset      = $_POST['yOffset'];
            } else {
                $textOverlay  = NULL;
                $fontFile     = NULL;
                $fontSize     = NULL;
                $fontColor    = NULL;
                $fontPosition = NULL;
                $xOffset      = NULL;
                $yOffset      = NULL;
            }
        }

        //
        // Handle each file
        //
        $fileCount = count($_FILES[$htmlFilesName]['name']);
        for ($i = 0; $i < $fileCount; $i++) {

            //
            // Images
            //
            if ($fileType == 'image') {
                // Save orig image info to database
                $originalRaw   = $_FILES[$htmlFilesName]['tmp_name'][$i];
                $simpleImage   = new SimpleImage($originalRaw);
                $info          = $simpleImage->get_original_info();
                $ext           = $info['format'];
                $width         = $info['width'];
                $height        = $info['height'];
                $orient        = $info['orientation'];
                $cartID       = $cartID;
                $imageID       = 'DEFAULT';
                $active        = 1;
                $filesName     = $_FILES[$htmlFilesName]['name'][$i];
                $name          = str_replace("." . substr($filesName, strrpos($filesName, '.') + 1), "", $filesName);
                $parentImageID = NULL;
                if ($productID !== NULL) {
                    $sortOrder     = (int)$image->getProductImageMaxSortOrder($productID) + 1;
                } else {
                    $sortOrder = NULL;
                }
                // If replaceing image, save new image with same sort order as old
                if (!empty($_POST['origImgID'])) {
                    $origImgID = $_POST['origImgID'];
                    if (!empty($_POST['imgSortOrder'.$origImgID])) {
                        $sortOrder = $_POST['imgSortOrder'.$origImgID];
                    }
                }
                
                if (  preg_match( '/^storefrontSlide/', $origImgID ) )
                {
                  $imageID = $image->saveStorefrontCarouselImage($cartID, 1, $origImgID, $name, $ext, $width, $height, $orient);
                } else {
                  $showInCarousel = 1;
                  $imageID       = $image->saveImageInfo($cartID, $imageID, $parentImageID, $active, $name, $ext, $width, $height,
                                                       $orient, $userID, $customerID, $categoryID, $productID, $sortOrder, $showInCarousel);
                  echo "
                    <table class='table table-bordered'>
                        <tr>
                            <td class='span2'>
                                Saved original:<br /> $width x $height
                            </td>
                            <td class='span6'>
                                <img src='" . cartPublicUrl . "getfile.php?cartID=" . $cartID . "&imageID=" . $imageID . "&w=" . $width . "&h=" . $height . "' />
                            </td>
                        </tr>";
                }
                
                
                // Make nested image folder if not exist
                $destPath = cartImagesDir . $cartID . '/' . $imageID;
                self::createNestedDirectory($destPath);

                // Where to save original image
                $origImgPath = $destPath . '/' . $imageID . '.' . $width . 'x' . $height . '.' . $ext;

                // If file already exists, rename (safe delete) existing file
                if (file_exists($origImgPath)) {
                    $j = 0;
                    while (TRUE) {
                        $renameFile = $destPath . '/' . $imageID . '.' . $width . 'x' . $height . '.' . $j . '.' . $ext;
                        if (file_exists($renameFile)) {
                            $j++;
                            continue;
                        }
                        \Errors::debugLogger('***** Renaming '.$origImgPath.' to '.$renameFile, 10);
                        if (!rename($origImgPath, $renameFile)) {
                            trigger_error('Could not rename existing file (j:' . $j . '): ' . $origImgPath, E_USER_NOTICE);
                            return false;
                        }
                        break;
                    }
                }

                // Save original image
                $failedToCreateImage = FALSE;
                if (!empty($textOverlay)) {
                    // Add text overlay
                    if (!$image->createImage($originalRaw, $origImgPath, $width, $height, $textOverlay, $fontFile, $fontSize,
                                             $fontColor, $fontPosition, $xOffset, $yOffset)) {
                        $failedToCreateImage = TRUE;
                    }
                } else {
                    
                    // COPYMove raw uploaded file from tmp
                    if (!copy($originalRaw, $origImgPath)) {
                    #if (!move_uploaded_file($originalRaw, $origImgPath)) {
                        $failedToCreateImage = TRUE;
                    } else {
                        #$originalRaw = $origImgPath;
                    }
                }
                if ($failedToCreateImage === TRUE) {
                    // Set image to inactive in database if fails
                    $image->setImageActive($cartID, $imageID, 0);
                    trigger_error('Could not save orig file: ' . $originalRaw, E_USER_NOTICE);
                    return false;
                }
                
                //
                // Replace image (if selected) for product options using old ID
                //
                if (!empty($_POST['origImgID'])) {
                    $origImgID = $_POST['origImgID'];
                    $newImgID = $imageID;
                    $image->replaceProductImage($cartID, $origImgID, $newImgID);
                }

                //
                // Create new sizes if selected
                //
                if (!empty($_POST['size'])) {
                    foreach ($_POST['size'] as $sizeReq) {
                        
                        // Option to allow stretching larger than orig
                        $allowLargerThanOrig = FALSE;
                        if ($allowLargerThanOrig === FALSE) {
                            // Only creates max size allowed and doesnt duplicate same size
                            if (($sizeReq > $height) && ($sizeReq > $width)) {
                                echo '<tr><td><cite>Skipped:</cite></td><td>' . $sizeReq . ' x ' . $sizeReq . '</td></tr>';
                                continue;
                            }
                        }

                        // Load original image data to get best fit for new size
                        $origSimpleImage = new SimpleImage($originalRaw);
                        if ($allowLargerThanOrig === TRUE) {
                            if ($width > $height) {
                                $origSimpleImage->fit_to_width($sizeReq);
                            } else {
                                $origSimpleImage->fit_to_height($sizeReq);
                            }
                        } else {
                            $origSimpleImage->best_fit($sizeReq, $sizeReq);
                        }
                        
                        // New dim
                        $bfW             = $origSimpleImage->get_width();
                        $bfH             = $origSimpleImage->get_height();
                        if ($bfW == $width
                                && $bfH == $height) {
                            // Skip this resize as it matches parent!
                            \Errors::debugLogger('***** Skip this resize as it matches parent!', 10);
                            continue;
                        }
                        // Save new image size info to db
                        $parentImageID   = $imageID;
                        $userID          = NULL;
                        $customerID      = NULL;
                        $categoryID      = NULL;
                        $productID       = NULL;
                        $sortOrder       = NULL;
                        $visibility      = NULL;
                        $subImageID      = $image->saveImageInfo($cartID, 'DEFAULT', $imageID, $active, $name, $ext, $bfW, $bfH,
                                                                 $orient, $userID, $customerID, $categoryID, $productID, $sortOrder, $visibility);
                        $newImg          = $destPath . '/' . $imageID . '.' . $bfW . 'x' . $bfH . '.' . $ext;
                        // If file already exists, rename existing file
                        if (file_exists($newImg)) {
                            $z = 0;
                            while (TRUE) {
                                $renameFile = $destPath . '/' . $imageID . '.' . $bfW . 'x' . $bfH . '.' . $z . '.' . $ext;
                                if (file_exists($renameFile)) {
                                    $z++;
                                    continue;
                                }
                                \Errors::debugLogger('***** Renaming '.$newImg.' to '.$renameFile, 10);
                                if (!rename($newImg, $renameFile)) {
                                    trigger_error('Could not rename existing file (z:' . $z . '): ' . $newImg, E_USER_NOTICE);
                                    return false;
                                }
                                break;
                            }
                        }
                        echo "<tr>
                                <td>Saved resized: $bfW x $bfH</td>
                                <td>
                                    <img src='" . cartPublicUrl . "getfile.php?cartID=" . $cartID . "&imageID=" . $subImageID . "&w=" . $bfW . "&h=" . $bfH . "' />
                                </td>
                            </tr>";
                        // Create resized image (best_fit)
                        $image->createImage($originalRaw, $newImg, $bfW, $bfH, $textOverlay, $fontFile, $fontSize, $fontColor,
                                            $fontPosition, $xOffset, $yOffset);
                    }
                }
                echo "</table>";
            }
        }
    }
    
    /**
     * createNestedDirectory
     * 
     * Creates nested directory path (creates parent folders if needed)
     * 
     * @static
     * 
     * @version v0.0.1
     * 
     * @since v0.0.1
     * 
     * @param string $dirPath
     * 
     * @return boolean
     */
    public static function createNestedDirectory($dirPath)
    {
        \Errors::debugLogger(__METHOD__, 10);
        \Errors::debugLogger(func_get_args(), 10);
        if (!is_dir($dirPath) && !@mkdir($dirPath, 0777, true)) {
            trigger_error('Could not create folder: ' . $dirPath, E_USER_ERROR);
            return false;
        }
    }

    /**
     * getDateTimeUTC
     * 
     * Gets the current time in UTC
     * 
     * @static
     * 
     * @param string $format Desired datetime format to return default of YYYY-MM-DD HH:MM:SS
     *
     * @return date UTC datetime of NOW in desired format
     * 
     * @todo optional param for desired timezone to convert server value to
     */
    public static function getDateTimeUTC($format = null)
    {
        \Errors::debugLogger(__METHOD__, 10);
        \Errors::debugLogger(func_get_args(), 10);
        // Default format if none passed
        if ($format === NULL) {
            $format = "Y-m-d H:i:s";
        }
        return $gmdate = gmdate($format);
    }

    /**
     * getServerDateTimeFromUTCDateTime
     *
     * Converts the provided UTC datetime to local server time
     * 
     * @static
     * 
     * @param date $utcDateTime UTC datetime to convert
     * @param string $format Desired datetime format to return default of YYYY-MM-DD HH:MM:SS
     * 
     * @return date $serverDateTime Server datetime of provided UTC datetime in desired format
     * 
     * @todo optional param for desired timezone to convert UTC value to
     */
    public static function getServerDateTimeFromUTCDateTime($utcDateTime, $dateTimeZone = NULL, $format = NULL)
    {
        \Errors::debugLogger(__METHOD__, 10);
        \Errors::debugLogger(func_get_args(), 10);
        // Default timezone
        if ($dateTimeZone === NULL) {
            // Attempt to use date.timezone declared in servers php.ini
            //$dateTimeZone = new DateTimeZone(ini_get('date.timezone'));
            // Manually set our own timezone if server does not declare a default
            //if (empty($dateTimeZone)) {
            $dateTimeZone = new DateTimeZone('America/New_York');
            //}
        } else {
            $dateTimeZone = new DateTimeZone($dateTimeZone);
        }
        // Default format
        if ($format === NULL) {
            $format = "Y-m-d H:i:s";
        }
        // Convert UTC DateTime to Server DateTime
        $serverDateTime = new DateTime($utcDateTime, new DateTimeZone('UTC'));
        $serverDateTime->setTimezone($dateTimeZone);
        return $serverDateTime->format($format);
    }

    /**
     * getPastDateTimeUTC
     * 
     * Subtracts x number of days from NOW or provided datetime in UTC
     * 
     * @param type $days Days to subtract from datetime
     * @param type $start Optional datetime start to subtract from, defaults to NOW (UTC)
     * @param string $format Optional format to return datetime in
     * 
     * @return date UTC DateTime after math applied
     */
    public static function getPastDateTimeUTC($days, $start = NULL, $format = NULL)
    {
        \Errors::debugLogger(__METHOD__, 10);
        \Errors::debugLogger(func_get_args(), 10);
        if (empty($start)) {
            $start = self::getDateTimeUTC();
        }
        if (empty($format)) {
            $format = "Y-m-d H:i:s";
        }
        $endDateTime = new \DateTime($start, new \DateTimeZone('UTC'));
        $endDateTime->sub(new \DateInterval("P" . $days . "D"));
        return $endDateTime->format($format);
    }

    /**
     * getFormattedNumber
     * 
     * Returns the number in the desired format
     * 
     * @param type $number Number to format
     * @param int $decimals Number of decimal points
     * @param type $decimalPoint Decimal point separator
     * @param type $thousandsSep Thousands separator
     * @param type $locale Locale to use defaults to local system
     * 
     * @return float Number in desired format
     */
    public static function getFormattedNumber($number, $decimals = NULL, $decimalPoint = NULL, $thousandsSep = NULL, $locale = NULL)
    {
        \Errors::debugLogger(__METHOD__, 10);
        \Errors::debugLogger(func_get_args(), 10);
        // Default Decimals
        if ($decimals === NULL) {
            $decimals = 2;
        }
        // Use locale if needed
        if ($decimalPoint === NULL || $thousandsSep === NULL) {
            // Default Locale
            if ($locale === NULL) {
                setlocale(LC_ALL, '');
            } else {
                setlocale(LC_ALL, $locale);
            }
            $locale = localeconv();
            // Default Decimal Point
            if ($decimalPoint === NULL) {
                $decimalPoint = $locale['decimal_point'];
            }
            // Default Thousands Separator
            if ($thousandsSep === NULL) {
                $thousandsSep = $locale['thousands_sep'];
            }
        }
        return number_format($number, $decimals, $decimalPoint, $thousandsSep);
    }

    /**
     * getVisitorIP
     *
     * Gets visitor IP address
     *
     * @return string Visitor IP address
     */
    public static function getVisitorIP()
    {
        \Errors::debugLogger(__METHOD__, 10);
        \Errors::debugLogger(func_get_args(), 10);
        $ip = $_SERVER['REMOTE_ADDR'];
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        return $ip;
    }

    /**
     * convertNLToBR
     * 
     * Converts newlines to <br />
     * 
     * @since v0.0.1
     * 
     * @param string $string
     * 
     * @return string
     */
    public static function convertNLToBR($string)
    {
        \Errors::debugLogger(__METHOD__, 10);
        \Errors::debugLogger(func_get_args(), 10);
        return str_ireplace(PHP_EOL, "<br />", $string);
    }

    /**
     * convertBRToNL
     * 
     * Converts <br /> to newlines (\r\n)
     * 
     * @since v0.0.1
     * 
     * @param string $string
     * 
     * @return string
     */
    public static function convertBRToNL($string)
    {
        \Errors::debugLogger(__METHOD__, 10);
        \Errors::debugLogger(func_get_args(), 10);
        $breaks = array("<br />", "<br>", "<br/>", "&lt;br /&gt;", "&lt;br/&gt;", "&lt;br&gt;");
        $r      = str_ireplace($breaks, PHP_EOL, $string);
        //var_dump($breaks, $string, $r);
        return $r;
    }
    
    /**
     * convertBRForJS
     * 
     * Converts <br /> to JavaScript newlines (\)
     * 
     * @since v0.0.1
     * 
     * @param string $string
     * 
     * @return string
     */
    public static function convertBRForJS($string)
    {
        \Errors::debugLogger(__METHOD__, 10);
        \Errors::debugLogger(func_get_args(), 10);
        $breaks = array("<br />", "<br>", "<br/>", "&lt;br /&gt;", "&lt;br/&gt;", "&lt;br&gt;");
        $r      = str_ireplace($breaks, '\\', $string);
        //var_dump($breaks, $string, $r);
        return $r;
    }

    /**
     * stripBR
     * 
     * Strips all <br /> from string
     * 
     * @since v0.0.1
     * 
     * @param string $string
     * 
     * @return string
     */
    public static function stripBR($string)
    {
        \Errors::debugLogger(__METHOD__, 10);
        \Errors::debugLogger(func_get_args(), 10);
        $breaks = array("<br />", "<br>", "<br/>", "<br />", "&lt;br /&gt;", "&lt;br/&gt;", "&lt;br&gt;");
        return str_ireplace($breaks, '', $string);
    }

    /**
     * getPaymentMethodCodeLogo
     * 
     * Gets logo of payment method code/type
     * 
     * @since v0.0.1
     * 
     * @param string $paymentMethodCode Code
     * 
     * @return string Image filename
     */
    public static function getPaymentMethodCodeLogo($paymentMethodCode)
    {
        if ($paymentMethodCode == 'AMEX') {
            $img = 'amex_logo_mini.gif';
        } elseif ($paymentMethodCode == 'VISA') {
            $img = 'visa_logo_mini.gif';
        } elseif ($paymentMethodCode == 'MC') {
            $img = 'mastercard_logo_mini.gif';
        } elseif ($paymentMethodCode == 'DISC') {
            $img = 'discover_logo_mini.gif';
        } elseif ($paymentMethodCode == 'CHK') {
            $img = 'check_icon_mini.png';
        } elseif ($paymentMethodCode == 'ECHK') {
            $img = 'echeck_logo_mini.gif';
        } elseif ($paymentMethodCode == 'WIRE') {
            $img = 'echeck_logo_mini.gif';
        } elseif ($paymentMethodCode == 'PAYP') {
            $img = 'paypal_logo_mini.gif';
        } elseif ($paymentMethodCode == 'CASH') {
            $img = 'cash.png';
        } else {
            $img = 'unknown.png';
        }
        return $img;
    }

    /**
     * getStateList
     * 
     * Returns list of state code => state label
     * 
     * @return array State Code => State Label
     */
    public static function getStateList()
    {
        \Errors::debugLogger(__METHOD__, 10);
        $stateList = array(
            '-- UNITED STATES --' => '-- UNITED STATES --',
            'AL'                  => 'Alabama',
            'AK'                  => 'Alaska',
            'AZ'                  => 'Arizona',
            'AR'                  => 'Arkansas',
            'CA'                  => 'California',
            'CO'                  => 'Colorado',
            'CT'                  => 'Connecticut',
            'DE'                  => 'Delaware',
            'DC'                  => 'District Of Columbia',
            'FL'                  => 'Florida',
            'GA'                  => 'Georgia',
            'HI'                  => 'Hawaii',
            'ID'                  => 'Idaho',
            'IL'                  => 'Illinois',
            'IN'                  => 'Indiana',
            'IA'                  => 'Iowa',
            'KS'                  => 'Kansas',
            'KY'                  => 'Kentucky',
            'LA'                  => 'Louisiana',
            'ME'                  => 'Maine',
            'MD'                  => 'Maryland',
            'MA'                  => 'Massachusetts',
            'MI'                  => 'Michigan',
            'MN'                  => 'Minnesota',
            'MS'                  => 'Mississippi',
            'MO'                  => 'Missouri',
            'MT'                  => 'Montana',
            'NE'                  => 'Nebraska',
            'NV'                  => 'Nevada',
            'NH'                  => 'New Hampshire',
            'NJ'                  => 'New Jersey',
            'NM'                  => 'New Mexico',
            'NY'                  => 'New York',
            'NC'                  => 'North Carolina',
            'ND'                  => 'North Dakota',
            'OH'                  => 'Ohio',
            'OK'                  => 'Oklahoma',
            'OR'                  => 'Oregon',
            'PA'                  => 'Pennsylvania',
            'RI'                  => 'Rhode Island',
            'SC'                  => 'South Carolina',
            'SD'                  => 'South Dakota',
            'TN'                  => 'Tennessee',
            'TX'                  => 'Texas',
            'UT'                  => 'Utah',
            'VT'                  => 'Vermont',
            'VA'                  => 'Virginia',
            'WA'                  => 'Washington',
            'WV'                  => 'West Virginia',
            'WI'                  => 'Wisconsin',
            'WY'                  => 'Wyoming',
            '-- CANADA --'        => '-- CANADA --',
            'AB'                  => 'Alberta ',
            'BC'                  => 'British Columbia ',
            'MB'                  => 'Manitoba ',
            'NB'                  => 'New Brunswick ',
            'NF'                  => 'Newfoundland and Labrador ',
            'NT'                  => 'Northwest Territories ',
            'NS'                  => 'Nova Scotia ',
            'NU'                  => 'Nunavut ',
            'ON'                  => 'Ontario ',
            'PE'                  => 'Prince Edward Island ',
            'PQ'                  => 'Quebec ',
            'SK'                  => 'Saskatchewan ',
            'YT'                  => 'Yukon Territory ',
            '-- AUSTRALIA -- '    => '-- AUSTRALIA -- ',
            'AC'                  => 'Australian Capital Territory ',
            'NW'                  => 'New South Wales ',
            'NO'                  => 'Northern Territory ',
            'QL'                  => 'Queensland ',
            'SA'                  => 'South Australia ',
            'TS'                  => 'Tasmania ',
            'VC'                  => 'Victoria ',
            'WS'                  => 'Western Australia ');
        return $stateList;
    }

    /**
     * getCountryList
     * 
     * Returns list of country code & country label
     * 
     * @return array Country Code => Country Label
     */
    public static function getCountryList()
    {
        \Errors::debugLogger(__METHOD__, 10);
        $countryList = array(
            'US' => 'United States',
            'UM' => 'United States Minor Outlying Islands',
            'CA' => 'Canada',
            'MX' => 'Mexico',
            'AU' => 'Australia',
            'AF' => 'Afghanistan',
            'AX' => 'Åland Islands',
            'AL' => 'Albania',
            'DZ' => 'Algeria',
            'AS' => 'American Samoa',
            'AD' => 'Andorra',
            'AO' => 'Angola',
            'AI' => 'Anguilla',
            'AQ' => 'Antarctica',
            'AG' => 'Antigua and Barbuda',
            'AR' => 'Argentina',
            'AM' => 'Armenia',
            'AW' => 'Aruba',
            'AT' => 'Austria',
            'AZ' => 'Azerbaijan',
            'BS' => 'Bahamas',
            'BH' => 'Bahrain',
            'BD' => 'Bangladesh',
            'BB' => 'Barbados',
            'BY' => 'Belarus',
            'BE' => 'Belgium',
            'BZ' => 'Belize',
            'BJ' => 'Benin',
            'BM' => 'Bermuda',
            'BT' => 'Bhutan',
            'BO' => 'Bolivia, Plurinational State of',
            'BQ' => 'Bonaire, Sint Eustatius and Saba',
            'BA' => 'Bosnia and Herzegovina',
            'BW' => 'Botswana',
            'BV' => 'Bouvet Island',
            'BR' => 'Brazil',
            'IO' => 'British Indian Ocean Territory',
            'BN' => 'Brunei Darussalam',
            'BG' => 'Bulgaria',
            'BF' => 'Burkina Faso',
            'BI' => 'Burundi',
            'KH' => 'Cambodia',
            'CM' => 'Cameroon',
            'CV' => 'Cape Verde',
            'KY' => 'Cayman Islands',
            'CF' => 'Central African Republic',
            'TD' => 'Chad',
            'CL' => 'Chile',
            'CN' => 'China',
            'CX' => 'Christmas Island',
            'CC' => 'Cocos (Keeling) Islands',
            'CO' => 'Colombia',
            'KM' => 'Comoros',
            'CG' => 'Congo',
            'CD' => 'Congo, the Democratic Republic of the',
            'CK' => 'Cook Islands',
            'CR' => 'Costa Rica',
            'CI' => 'Côte d\'Ivoire',
            'HR' => 'Croatia',
            'CU' => 'Cuba',
            'CW' => 'Curaçao',
            'CY' => 'Cyprus',
            'CZ' => 'Czech Republic',
            'DK' => 'Denmark',
            'DJ' => 'Djibouti',
            'DM' => 'Dominica',
            'DO' => 'Dominican Republic',
            'EC' => 'Ecuador',
            'EG' => 'Egypt',
            'SV' => 'El Salvador',
            'GQ' => 'Equatorial Guinea',
            'ER' => 'Eritrea',
            'EE' => 'Estonia',
            'ET' => 'Ethiopia',
            'FK' => 'Falkland Islands (Malvinas)',
            'FO' => 'Faroe Islands',
            'FJ' => 'Fiji',
            'FI' => 'Finland',
            'FR' => 'France',
            'GF' => 'French Guiana',
            'PF' => 'French Polynesia',
            'TF' => 'French Southern Territories',
            'GA' => 'Gabon',
            'GM' => 'Gambia',
            'GE' => 'Georgia',
            'DE' => 'Germany',
            'GH' => 'Ghana',
            'GI' => 'Gibraltar',
            'GR' => 'Greece',
            'GL' => 'Greenland',
            'GD' => 'Grenada',
            'GP' => 'Guadeloupe',
            'GU' => 'Guam',
            'GT' => 'Guatemala',
            'GG' => 'Guernsey',
            'GN' => 'Guinea',
            'GW' => 'Guinea-Bissau',
            'GY' => 'Guyana',
            'HT' => 'Haiti',
            'HM' => 'Heard Island and McDonald Islands',
            'VA' => 'Holy See (Vatican City State)',
            'HN' => 'Honduras',
            'HK' => 'Hong Kong',
            'HU' => 'Hungary',
            'IS' => 'Iceland',
            'IN' => 'India',
            'ID' => 'Indonesia',
            'IR' => 'Iran, Islamic Republic of',
            'IQ' => 'Iraq',
            'IE' => 'Ireland',
            'IM' => 'Isle of Man',
            'IL' => 'Israel',
            'IT' => 'Italy',
            'JM' => 'Jamaica',
            'JP' => 'Japan',
            'JE' => 'Jersey',
            'JO' => 'Jordan',
            'KZ' => 'Kazakhstan',
            'KE' => 'Kenya',
            'KI' => 'Kiribati',
            'KP' => 'Korea, Democratic People\'s Republic of',
            'KR' => 'Korea, Republic of',
            'KW' => 'Kuwait',
            'KG' => 'Kyrgyzstan',
            'LA' => 'Lao People\'s Democratic Republic',
            'LV' => 'Latvia',
            'LB' => 'Lebanon',
            'LS' => 'Lesotho',
            'LR' => 'Liberia',
            'LY' => 'Libya',
            'LI' => 'Liechtenstein',
            'LT' => 'Lithuania',
            'LU' => 'Luxembourg',
            'MO' => 'Macao',
            'MK' => 'Macedonia, The Former Yugoslav Republic of',
            'MG' => 'Madagascar',
            'MW' => 'Malawi',
            'MY' => 'Malaysia',
            'MV' => 'Maldives',
            'ML' => 'Mali',
            'MT' => 'Malta',
            'MH' => 'Marshall Islands',
            'MQ' => 'Martinique',
            'MR' => 'Mauritania',
            'MU' => 'Mauritius',
            'YT' => 'Mayotte',
            'FM' => 'Micronesia, Federated States of',
            'MD' => 'Moldova, Republic of',
            'MC' => 'Monaco',
            'MN' => 'Mongolia',
            'ME' => 'Montenegro',
            'MS' => 'Montserrat',
            'MA' => 'Morocco',
            'MZ' => 'Mozambique',
            'MM' => 'Myanmar',
            'NA' => 'Namibia',
            'NR' => 'Nauru',
            'NP' => 'Nepal',
            'NL' => 'Netherlands',
            'NC' => 'New Caledonia',
            'NZ' => 'New Zealand',
            'NI' => 'Nicaragua',
            'NE' => 'Niger',
            'NG' => 'Nigeria',
            'NU' => 'Niue',
            'NF' => 'Norfolk Island',
            'MP' => 'Northern Mariana Islands',
            'NO' => 'Norway',
            'OM' => 'Oman',
            'PK' => 'Pakistan',
            'PW' => 'Palau',
            'PS' => 'Palestine, State of',
            'PA' => 'Panama',
            'PG' => 'Papua New Guinea',
            'PY' => 'Paraguay',
            'PE' => 'Peru',
            'PH' => 'Philippines',
            'PN' => 'Pitcairn',
            'PL' => 'Poland',
            'PT' => 'Portugal',
            'PR' => 'Puerto Rico',
            'QA' => 'Qatar',
            'RE' => 'Réunion',
            'RO' => 'Romania',
            'RU' => 'Russian Federation',
            'RW' => 'Rwanda',
            'BL' => 'Saint Barthélemy',
            'SH' => 'Saint Helena, Ascension and Tristan da Cunha',
            'KN' => 'Saint Kitts and Nevis',
            'LC' => 'Saint Lucia',
            'MF' => 'Saint Martin (French part)',
            'PM' => 'Saint Pierre and Miquelon',
            'VC' => 'Saint Vincent and the Grenadines',
            'WS' => 'Samoa',
            'SM' => 'San Marino',
            'ST' => 'Sao Tome and Principe',
            'SA' => 'Saudi Arabia',
            'SN' => 'Senegal',
            'RS' => 'Serbia',
            'SC' => 'Seychelles',
            'SL' => 'Sierra Leone',
            'SG' => 'Singapore',
            'SX' => 'Sint Maarten (Dutch part)',
            'SK' => 'Slovakia',
            'SI' => 'Slovenia',
            'SB' => 'Solomon Islands',
            'SO' => 'Somalia',
            'ZA' => 'South Africa',
            'GS' => 'South Georgia and the South Sandwich Islands',
            'SS' => 'South Sudan',
            'ES' => 'Spain',
            'LK' => 'Sri Lanka',
            'SD' => 'Sudan',
            'SR' => 'Suriname',
            'SJ' => 'Svalbard and Jan Mayen',
            'SZ' => 'Swaziland',
            'SE' => 'Sweden',
            'CH' => 'Switzerland',
            'SY' => 'Syrian Arab Republic',
            'TW' => 'Taiwan, Province of China',
            'TJ' => 'Tajikistan',
            'TZ' => 'Tanzania, United Republic of',
            'TH' => 'Thailand',
            'TL' => 'Timor-Leste',
            'TG' => 'Togo',
            'TK' => 'Tokelau',
            'TO' => 'Tonga',
            'TT' => 'Trinidad and Tobago',
            'TN' => 'Tunisia',
            'TR' => 'Turkey',
            'TM' => 'Turkmenistan',
            'TC' => 'Turks and Caicos Islands',
            'TV' => 'Tuvalu',
            'UG' => 'Uganda',
            'UA' => 'Ukraine',
            'AE' => 'United Arab Emirates',
            'GB' => 'United Kingdom',
            'UY' => 'Uruguay',
            'UZ' => 'Uzbekistan',
            'VU' => 'Vanuatu',
            'VE' => 'Venezuela, Bolivarian Republic of',
            'VN' => 'Viet Nam',
            'VG' => 'Virgin Islands, British',
            'VI' => 'Virgin Islands, U.S.',
            'WF' => 'Wallis and Futuna',
            'EH' => 'Western Sahara',
            'YE' => 'Yemen',
            'ZM' => 'Zambia',
            'ZW' => 'Zimbabwe'
        );
        return $countryList;
    }

}