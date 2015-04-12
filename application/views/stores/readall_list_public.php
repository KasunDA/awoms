<table style="margin-left: 50px;">
    <tr>
        <td>
            <h1><?= $_SESSION['brand']['brandName']; ?> Store Locator</h1>
            <table width="85%">
                <tr>
                    <td colspan="2">
                        <p><strong>Find a store in your area or search by state</strong></p>
                    </td>
                </tr>
                <tr>
                    <td>Zip Code:</td>
                    <td><input type="text" name="inp_zipcode" size="5"/></td>
                </tr>
                <tr>
                    <td>Search Radius:</td>
                    <td>
                        <select name="inp_searchRadius">
                            <option value="25">25 Miles</option>
                            <option value="50">50 Miles</option>
                            <option value="100" selected>100 Miles</option>
                            <option value="200">200 Miles</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td>
                        <input type="button" value="FIND"/>
                    </td>
                </tr>
            </table>
        </td>
        <td>
            <img src="/css/<?= $_SESSION['brand']['brandLabel']; ?>/images/items/store_locator_map.png" alt="" width="632" height="424" border="0" usemap="#MapStore" class="no-border" />
            <!-- Map -->
        </td>
    </tr>
</table>


<?php
// @TODO:
// Filter:
// --State?
// --Zip?

/* Display Stores */
$cols  = 4;
$table = "<table class='store_locator'>";
$colOn = 1;
$i     = 0;
foreach ($items as $item)
{

    // Coding:
//    if ($item['coding'] != "O")
//    { continue; }

    $itemID      = $item['storeID'];
    $storeNumber = $item['storeNumber'];
    $storePhone  = $item['phone'];
    $storeEmail  = $item['email'];

    // Address
    $storeAddressLine1 = $item['address']['line1'];
    $storeAddressLine2 = $item['address']['line2'];
    $storeAddressLine3 = $item['address']['line3'];
    $storeAddress      = $storeAddressLine1;
    if (!empty($storeAddressLine2))
    {
        $storeAddress .= "<br />" . $storeAddressLine2;
    }
    if (!empty($storeAddressLine3))
    {
        $storeAddress .= "<br />" . $storeAddressLine3;
    }
    $storeState   = $item['address']['stateProvinceCounty'];
    $storeCity    = $item['address']['city'];
    $storeZip     = $item['address']['zipPostcode'];
    $storeAddress = $storeAddress . "<br/>" . $storeCity . ", " . $storeState . " " . $storeZip;

    $readLink = BRAND_URL . 'stores/read/' . $itemID . '/' . $storeState . '/' . $storeCity . '/' . $storeNumber;

    // Coding?
//    switch ($item['coding']) {
//        case "N":
//            $activeClass = "warning";
//            break;
//        case "T":
//        case "O/C":
//            $activeClass = "info";
//            break;
//        case "C":
//            $activeClass = "failure";
//            break;
//        case "O":
//            $activeClass = "success";
//            break;
//        default:
//            $activeClass = "failure";
//            break;
//    }
//echo "<td><div class='alert " . $activeClass . " no-img center'>" . $item['coding'] . "</div></td>";

    $colData = "
<span class='body-text-bold'><a href='" . $readLink . "'>" . $_SESSION['brand']['brandName'] . " " . $storeCity . "</a></span><br />
" . $storeAddress . "<br />
Phone: " . $storePhone . "<br />
<a href='mailto:" . $storeEmail . "'>" . $storeEmail . "</a>";

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
$table .= "</table>";
echo $table;
?>