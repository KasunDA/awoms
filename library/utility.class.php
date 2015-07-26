<?php

/**
 * Utility class
 *
 * Utilities methods used globally
 *
 * PHP version 5
 *
 * @author    Brock Hensley <Brock@brockhensley.com>
 * @license   MIT/X see LICENSE
 * @version v0.0.1
 */
class Utility
{
    /**
     * Log Level
     *
     * @var int $logLevel Config log level used for this class
     */
    protected static $logLevel = 7000;

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
        Errors::debugLogger(__METHOD__, Utility::$logLevel);
        Errors::debugLogger(func_get_args(), Utility::$logLevel);
        if (!is_dir($dirPath) && !@mkdir($dirPath, 0777, true))
        {
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
        Errors::debugLogger(__METHOD__, Utility::$logLevel);
        Errors::debugLogger(func_get_args(), Utility::$logLevel);
        // Default format if none passed
        if ($format === NULL)
        {
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
        Errors::debugLogger(__METHOD__, Utility::$logLevel);
        Errors::debugLogger(func_get_args(), Utility::$logLevel);
        // Default timezone
        if ($dateTimeZone === NULL)
        {
            // Attempt to use date.timezone declared in servers php.ini
            //$dateTimeZone = new DateTimeZone(ini_get('date.timezone'));
            // Manually set our own timezone if server does not declare a default
            //if (empty($dateTimeZone)) {
            $dateTimeZone = new DateTimeZone('America/New_York');
            //}
        }
        else
        {
            $dateTimeZone = new DateTimeZone($dateTimeZone);
        }
        // Default format
        if ($format === NULL)
        {
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
        Errors::debugLogger(__METHOD__, Utility::$logLevel);
        Errors::debugLogger(func_get_args(), Utility::$logLevel);
        if (empty($start))
        {
            $start = self::getDateTimeUTC();
        }
        if (empty($format))
        {
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
        Errors::debugLogger(__METHOD__, Utility::$logLevel);
        Errors::debugLogger(func_get_args(), Utility::$logLevel);
        // Default Decimals
        if ($decimals === NULL)
        {
            $decimals = 2;
        }
        // Use locale if needed
        if ($decimalPoint === NULL || $thousandsSep === NULL)
        {
            // Default Locale
            if ($locale === NULL)
            {
                setlocale(LC_ALL, '');
            }
            else
            {
                setlocale(LC_ALL, $locale);
            }
            $locale = localeconv();
            // Default Decimal Point
            if ($decimalPoint === NULL)
            {
                $decimalPoint = $locale['decimal_point'];
            }
            // Default Thousands Separator
            if ($thousandsSep === NULL)
            {
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
        Errors::debugLogger(__METHOD__.': Intentionally NOT logging IP address to log file', Utility::$logLevel);
        
        $ip = "Unknown";
        if (!empty($_SERVER['REMOTE_ADDR']))
        {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        if (!empty($_SERVER['HTTP_CLIENT_IP']))
        {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
        {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        return $ip;
    }

    /**
     * getTemplateFileLocations
     *
     * Searches for appropriate custom template files and returns array of valid file paths
     *
     * Example:
     *  views/templates/BRAND/THEME/CONTROLLER/name.php
     *  views/templates/BRAND/THEME/name.php
     *  views/name.php
     *
     * @param string $name
     * @param boolean $customFirst set to false to have default files be searched first
     * @param boolean $includeAllFound set to true to include all found files
     *
     * @returns array
     */
    public static function getTemplateFileLocations($name, $customFirst = TRUE, $includeAllFound = FALSE)
    {

        // Views folder
        $viewsFolder = ROOT . DS . 'application' . DS . 'views' . DS;

        // Possible locations
        $fileLocations = array();

        $fileLocations[] = $viewsFolder . 'templates' . DS . BRAND_LABEL . DS . BRAND_THEME . DS . $_SESSION['controller'] . DS . $name . '.php';
        $fileLocations[] = $viewsFolder . 'templates' . DS . BRAND_LABEL . DS . BRAND_THEME . DS . $name . '.php';
        $fileLocations[] = $viewsFolder . DS . $_SESSION['controller'] . DS . $name . '.php';
        $fileLocations[] = $viewsFolder . $name . '.php';

        // Default or Custom takes precedence?
        if (!$customFirst)
        {
            $fileLocations = array_reverse($fileLocations);
        }

        $i = 0;
        foreach ($fileLocations as $fileLoc)
        {
            if (!file_exists($fileLoc))
            {
                unset($fileLocations[$i]);
            }
            else
            {
                if (!$includeAllFound)
                {
                    $fileLocations   = array();
                    $fileLocations[] = $fileLoc;
                    break;
                }
            }
            $i++;
        }

        return $fileLocations;
    }

    /**
     * Returns "include(x)" results into variable instead of instantly parsing them as the default include/get_file_contents (which shows code) does
     *
     * @param type $filename
     *
     * @return boolean
     */
    public static function get_include_contents($filename)
    {
        if (is_file($filename))
        {
            ob_start();
            include $filename;
            return ob_get_clean();
        }
        return false;
    }

    /**
     * Ensures things like "Goin' Postal" are displayed as "Goin&#039; Postal"
     * Protects against injection attacks
     *
     * @uses htmlspecialchars
     *
     * @param array|string $raw
     * @return array|string
     */
    public static function makeRawDbTextSafeForHtmlDisplay($raw)
    {
        array_walk_recursive($raw,
                             function (&$value)
            {
                // $value = htmlentities($value, ENT_QUOTES);
                $value = htmlspecialchars($value, ENT_QUOTES, "UTF-8", FALSE);
            });
        return $raw;
    }

    /**
     * Ensures things like "Goin&#039; Postal" are displayed as "Goin' Postal"
     *
     * @uses htmlspecialchars_decode
     *
     * @param array|string $raw
     * @return array|string
     */
    public static function makeSafeDbTextReadyForHtmlDisplay($raw)
    {
        array_walk_recursive($raw,
                             function (&$value)
            {

                //echo "<br />Cleaning value: ".$value;
                //$value = htmlspecialchars($value, ENT_QUOTES, "UTF-8", FALSE);
                $value = htmlspecialchars_decode($value, ENT_NOQUOTES);
            });
        return $raw;
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
        Errors::debugLogger(__METHOD__, Utility::$logLevel);
        Errors::debugLogger(func_get_args(), Utility::$logLevel);
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
        Errors::debugLogger(__METHOD__, Utility::$logLevel);
        Errors::debugLogger(func_get_args(), Utility::$logLevel);
        $breaks = array("<br />", "<br>", "<br/>", "&lt;br /&gt;", "&lt;br/&gt;", "&lt;br&gt;");
        $r      = str_ireplace($breaks, PHP_EOL, $string);
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
        Errors::debugLogger(__METHOD__, Utility::$logLevel);
        Errors::debugLogger(func_get_args(), Utility::$logLevel);
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
        if ($paymentMethodCode == 'AMEX')
        {
            $img = 'amex_logo_mini.gif';
        }
        elseif ($paymentMethodCode == 'VISA')
        {
            $img = 'visa_logo_mini.gif';
        }
        elseif ($paymentMethodCode == 'MC')
        {
            $img = 'mastercard_logo_mini.gif';
        }
        elseif ($paymentMethodCode == 'DISC')
        {
            $img = 'discover_logo_mini.gif';
        }
        elseif ($paymentMethodCode == 'CHK')
        {
            $img = 'check_icon_mini.png';
        }
        elseif ($paymentMethodCode == 'ECHK')
        {
            $img = 'echeck_logo_mini.gif';
        }
        elseif ($paymentMethodCode == 'WIRE')
        {
            $img = 'echeck_logo_mini.gif';
        }
        elseif ($paymentMethodCode == 'PAYP')
        {
            $img = 'paypal_logo_mini.gif';
        }
        elseif ($paymentMethodCode == 'CASH')
        {
            $img = 'cash.png';
        }
        else
        {
            $img = 'unknown.png';
        }
        return $img;
    }

    /**
     * randomPassphrase
     *
     * Generate random passphrase, simple or complex characters with optional length
     *
     * @since v0.0.1
     *
     * @static
     *
     * @access public
     *
     * @param int $len Optional length of password, defaults to 12
     * @param boolean $simple Optional flag for simple characters only (no punctuation etc)
     *
     * @return string Passphrase
     */
    public static function randomPassphrase($len = NULL, $simple = NULL)
    {
        if ($len === NULL)
        {
            $len = 12;
        }
        if ($simple !== NULL)
        {
            return substr(hash('sha512', rand()), 0, $len);
        }
        $r = '';
        for ($i = 0; $i < $len; $i++)
        {
            $r .= chr(mt_rand(33, 126));
        }
        return $r;
    }

    // This function returns Longitude & Latitude from zip code.
    public static function getLnt($zip)
    {
        Errors::debugLogger(__METHOD__);
        $url = "http://maps.googleapis.com/maps/api/geocode/json?address=".urlencode($zip)."&sensor=false";
        $result_string = file_get_contents($url);
        $result = json_decode($result_string, true);
        $result1[]=$result['results'][0];
        $result2[]=$result1[0]['geometry'];
        $result3[]=$result2[0]['location'];
        return $result3[0];
    }

    /**
     * Returns the distance in miles between two zip codes
     * 
     * @param int $zip1
     * @param int $zip2
     *
     * @return int
     */
    public static function GetDistanceInMilesBetweenZipCodes($zip1, $zip2)
    {
        Errors::debugLogger(__METHOD__);
        $first_lat = self::getLnt($zip1);
        $next_lat = self::getLnt($zip2);
        $lat1 = $first_lat['lat'];
        $lon1 = $first_lat['lng'];
        $lat2 = $next_lat['lat'];
        $lon2 = $next_lat['lng'];
        $theta=$lon1-$lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +
        cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
        cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        Errors::debugLogger(__METHOD__.': Returning Distance of: '.$miles);
        return $miles;
    }

    /**
     * Generates <option> list for State select list use in template
     */
    public static function GetStateChoiceList($SelectedState = FALSE)
    {
        $choiceList = "<option value=''>-- None --</option>";
        $states     = self::getStateList();
        if (!empty($states))
        {
            $choiceList = "<option value=''>-- SELECT --</option>";
            $optgroup   = NULL;
            foreach ($states as $stateCode => $stateLabel)
            {
                // Group by Country
                if (preg_match('/^--/', $stateCode))
                {
                    if ($optgroup != NULL)
                    {
                        $choiceList .= "</optgroup>";
                    }
                    $optgroup = $stateLabel;
                    $choiceList .= "<optgroup label='" . $stateLabel . "'>";
                    continue;
                }

                $selected = '';
                if ($SelectedState !== FALSE && $SelectedState !== "ALL" && $stateCode == $SelectedState)
                {
                    $selected = " selected";
                }
                $choiceList .= "
                    <option value='" . $stateCode . "'" . $selected . ">"
                    . $stateLabel .
                    "</option>";
            }
            if ($optgroup != NULL)
            {
                $choiceList .= "</optgroup>";
            }
        }
        return $choiceList;
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
        Errors::debugLogger(__METHOD__, Utility::$logLevel);
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
     * Generates <option> list for Country select list use in template
     */
    public static function GetCountryChoiceList($SelectedCountry = FALSE)
    {
        $choiceList = "<option value=''>-- None --</option>";
        $countries  = self::getCountryList();
        if (!empty($countries))
        {
            $choiceList = "<option value=''>-- SELECT --</option>";
            foreach ($countries as $code => $label)
            {
                $selected = '';
                if ($SelectedCountry !== FALSE && $SelectedCountry !== "ALL" && $code == $SelectedCountry)
                {
                    $selected = " selected";
                }
                $choiceList .= "
                    <option value='" . $code . "'" . $selected . ">"
                    . $label .
                    "</option>";
            }
        }
        return $choiceList;
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
        Errors::debugLogger(__METHOD__, Utility::$logLevel);
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
