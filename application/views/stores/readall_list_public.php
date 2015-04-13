<?php
$fileLocations = Utility::getTemplateFileLocations('readall_list_public_locator_form');
foreach ($fileLocations as $fileLoc)
{
    include($fileLoc);
}

$cols = 4;
$table = "<table class='store_locator'>";
$colOn = 1;
$i = 0;

// Zip Filter?
$zipFilter = FALSE;
if (isset($_POST['inp_zipcode']) && isset($_POST['inp_searchRadius']))
{
    $zip = $_POST['inp_zipcode'];
    $zipMiles = $_POST['inp_searchRadius'];
    if (preg_match('/\d+/', $zip)) {
        $zipFilter = $zip;
        $table = "<h3>Stores within $zipMiles miles of ".$zipFilter."</h3>".$table;
    }
}
// State Filter? (after zip and if zip is entered ignore entered state)
$stateFilter = FALSE;
if (!$zipFilter && !empty($_SESSION['query']))
{
    $filter = $_SESSION['query'];
    if (!empty($filter))
    {
        $filter = $filter[0];
    }
    if (preg_match('/[a-zA-Z]+/', $filter))
    {
        $stateFilter = strtoupper($filter);
        $table = "<h3>Stores in state ".$stateFilter."</h3>".$table;
    }
}

$displayedStoreCount = 0;
foreach ($items as $item)
{
    // Only show "Open" stores
    if (!empty($item['coding'])
            && $item['coding'] != "O")
    {
        Errors::debugLogger(__METHOD__.'@'.__LINE__.': Skipping Non-Open store ('.$item['storeID'].')...');
        continue;
    }

    // Zip Filter?
    if (!empty($item['address']))
    {
        $storeZip = $item['address']['zipPostcode'];
    } else { $storeZip = ""; }

    $distance = 0;
    if (!empty($zipFilter))
    {
        if (empty($storeZip))
        {
            Errors::debugLogger(__METHOD__.'@'.__LINE__.': Skipping store with empty zip ('.$storeZip.') due to Zip Filter ('.$zipFilter.' x '.$zipMiles.'mi ...');
            continue;
        }

        $distance = Utility::GetDistanceInMilesBetweenZipCodes($storeZip, $zipFilter);
        if ($distance > $zipMiles)
        {
            Errors::debugLogger(__METHOD__.'@'.__LINE__.': Skipping store ('.$storeZip.') due to Zip Filter ('.$zipFilter.' x '.$zipMiles.'mi ...');
            continue;
        }
    }

    // State Filter? (after zip and if zip is entered, ignore entered state)
    if (!empty($item['address']))
    {
        $storeState = $item['address']['stateProvinceCounty'];
    } else { $storeState = ""; }
    if (empty($zipFilter))
    {
        if (!empty($stateFilter) && $storeState != $stateFilter)
        {
            Errors::debugLogger(__METHOD__.'@'.__LINE__.': Skipping store ('.$storeState.') due to State Filter ('.$stateFilter.')...');
            continue;
        }
    }

    // Add store to display table
    $displayedStoreCount++;
    $itemID = $item['storeID'];
    $storeNumber = $item['storeNumber'];
    $storePhone = $item['phone'];
    $storeEmail = $item['email'];

    // Address
    if (!empty($item['address']))
    {
        $storeAddressLine1 = $item['address']['line1'];
        $storeAddressLine2 = $item['address']['line2'];
        $storeAddressLine3 = $item['address']['line3'];
        $storeAddress = $storeAddressLine1;
        if (!empty($storeAddressLine2))
        {
            $storeAddress .= "<br />" . $storeAddressLine2;
        }
        if (!empty($storeAddressLine3))
        {
            $storeAddress .= "<br />" . $storeAddressLine3;
        }
        $storeCity = $item['address']['city'];
        $storeAddress = $storeAddress . "<br/>" . $storeCity . ", " . $storeState . " " . $storeZip;
        $readLink = BRAND_URL . 'stores/read/' . $itemID . '/' . $storeState . '/' . $storeCity . '/' . $storeNumber;
    } else {
        $storeAddress = "";
        $readLink = BRAND_URL . 'stores/read/' . $itemID . '/' . $item['storeName'];
    }

    // Construct store listing with available data
    if (!empty($item['address']))
    {
        $storeLabel = $storeCity;
        $storeAddress = $storeAddress."<br />";
    } else {
        $storeLabel = $item['storeName'];
        $storeAddress = "";
    }

    if (!empty($storePhone))
    {
        $storePhone = "Phone: " . $storePhone . "<br />";
    } else {
        $storePhone = "";
    }

    if (!empty($storeEmail))
    {
        $storeEmail = "<a href='mailto:" . $storeEmail . "'>" . $storeEmail . "</a>";
    } else {
        $storeEmail = "";
    }

    $colData = "
<span class='body-text-bold'><a href='" . $readLink . "'>" . $_SESSION['brand']['brandName'] . " " . $storeLabel . "</a></span>
<br />
" . $storeAddress . $storePhone . $storeEmail;

    // Table row/cell construction
    if ($colOn == 1)
    {
        // New Row
        $colData = "<tr><td>" . $colData . "</td>";
    }
    elseif ($colOn == $cols)
    {
        // End Row
        $colData = "<td>" . $colData . "</td></tr>";
    }
    elseif ($i == (count($items) - 1))
    {
        // Last entry (fill in blank cells as needed)
        $blanks = "";
        for ($j = 0; $j < ($cols - $colOn); $j++)
        {
            $blanks .= "<td>&nbsp;</td>";
        }
        $colData = "<td>" . $colData . "</td>" . $blanks . "</tr>";
    }
    else
    {
        // Normal Col
        $colData = "<td>" . $colData . "</td>";
    }
    $table .= $colData;
    $colOn++;
    $i++;
}

if ($displayedStoreCount == 0)
{
    $table .= "No stores found";
}

$table .= "</table>";
echo $table;