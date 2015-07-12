<?php
// Brand
$Brand = new Brand();
$brand = $Brand->getSingle(array('brandID' => $store['brandID']));

// Address
if (!empty($store['address']))
{
    $storeAddressLine1 = $store['address']['line1'];
    $storeAddressLine2 = $store['address']['line2'];
    $storeAddressLine3 = $store['address']['line3'];
    $storeAddress      = $storeAddressLine1;
    if (!empty($storeAddressLine2))
    {
        $storeAddress .= "<br />" . $storeAddressLine2;
    }
    if (!empty($storeAddressLine3))
    {
        $storeAddress .= "<br />" . $storeAddressLine3;
    }
    $storeState   = $store['address']['stateProvinceCounty'];
    $storeCity    = $store['address']['city'];
    $storeZip     = $store['address']['zipPostcode'];
    $storeAddress = $storeAddress . "<br/>" . $storeCity . ", " . $storeState . " " . $storeZip;
}
else
{
    $storeAddress = "";
    $storeState   = "";
    $storeCity    = "";
}

if (empty($store['storeName']))
{
    $storeName = $storeCity;
} else {
    $storeName = $store['storeName'];
}

// Hours
/*
$days  = array('hrsMon' => 'Monday',
    'hrsTue' => 'Tuesday',
    'hrsWed' => 'Wednesday',
    'hrsThu' => 'Thursday',
    'hrsFri' => 'Friday',
    'hrsSat' => 'Saturday',
    'hrsSun' => 'Sunday');
$hours = "<h2>Hours</h2>";
$showHours = FALSE;
foreach ($days as $code => $codeLabel)
{
    if (!empty($store[$code]))
    {
        $showHours = TRUE;
        $hours .= "<tr><td>" . $codeLabel . "</td><td>" . $store[$code] . "</td></tr>";
    }
}
if (!$showHours)
{
    $hours = FALSE;
}
*/

// Images
$storeImages = FALSE;
$store['images'] = array();
if (!empty($store['pic1']))
{
    $store['images'][] = $store['pic1'];
}
if (!empty($store['pic2']))
{
    $store['images'][] = $store['pic2'];
}
if (!empty($store['pic3']))
{
    $store['images'][] = $store['pic3'];
}

if (!empty($store['images']))
{
    $storeImages = "<tr><td colspan='2'>";

    /* Display Multiple Store Images */
    $cols  = count($store['images']);
    $table = "<table>";
    $colOn = 1;
    $i     = 0;
    foreach ($store['images'] as $storeImage)
    {
        $width = round(900 / $cols) - 20;
        $showImage = "<img src='" . $storeImage . "' style='width:".$width."px;'/>";

        // Table row/cell construction
        if ($colOn == 1)
        {
            // New Row
            $colData = "<tr><td>" . $showImage . "</td>";
        }
        elseif ($colOn == $cols)
        {
            // End Row
            $colData = "<td>" . $showImage . "</td></tr>";
        }
        elseif ($i == (count($store['images']) - 1))
        {
            // Last entry (fill in blank cells as needed)
            $blanks = "";
            for ($j = 0; $j < ($cols - $colOn); $j++)
            {
                $blanks .= "<td>&nbsp;</td>";
            }
            $colData = "<td>" . $showImage . "</td>" . $blanks . "</tr>";
        }
        else
        {
            // Normal Col
            $colData = "<td>" . $showImage . "</td>";
        }
        $table .= $colData;
        $colOn++;
        $i++;
    }
    $table .= "</table>";
    $storeImages .= $table."</td></tr>";
}
?>

<!-- MAIN TBL -->
<table style="width:100%;">
    <tr>
        <td colspan="2">
            <header>
                <h1>Locations &amp; Services</h1>
                <h2><?= $brand['brandName'] . ' ' . $storeName; ?></h2>
            </header>
        </td>
    </tr>
    <?php
    // Images
    if (!empty($storeImages))
    {
        echo $storeImages;
    }
    ?>
    <tr>
        <!-- LEFT COL -->
        <td style="width:300px;">
            <table class="bordered_outside">
                <?php
                // Address
                if (!empty($storeAddress))
                {
                ?>
                <tr>
                    <td><strong>Address</strong></td>
                    <td>
                        <?= $storeAddress; ?>
                        <?php
                        if (!empty($store['map']))
                        {
                        ?><br />
                            <a href='#map'>Map &amp; Directions</a>
                        <?php
                        }
                        ?>
                    </td>
                </tr>
                <?php
                }

                // Phone
                if (!empty($store['phone']))
                {
                    ?>
                    <tr>
                        <td>
                            <strong>Phone:</strong>
                        </td>
                        <td>
                            <?= $store['phone']; ?>
                        </td>
                    </tr>
                    <?php
                }

                // Toll Free
                if (!empty($store['tollFree']))
                {
                    ?>
                    <tr>
                        <td>
                            <strong>Toll Free:</strong>
                        </td>
                        <td>
                            <?= $store['tollFree']; ?>
                        </td>
                    </tr>
                    <?php
                }

                // Fax
                if (!empty($store['fax']))
                {
                    ?>
                    <tr>
                        <td>
                            <strong>Fax:</strong>
                        </td>
                        <td>
                            <?= $store['fax']; ?>
                        </td>
                    </tr>
                    <?php
                }

                // Email
                if (!empty($store['email']))
                {
                    ?>
                    <tr>
                        <td>
                            <strong>Email:</strong>
                        </td>
                        <td>
                            <a href='mailto:<?= $store['email']; ?>'><?= $store['email']; ?></a>
                        </td>
                    </tr>
                    <?php
                }

                // Hours
                if (!empty($store['hours']))
                {
                ?>
                    <tr>
                        <td>
                            <strong>Hours:</strong>
                        </td>
                        <td>
                            <?php echo $store['hours']; ?>
                        </td>
                    </tr>
                <?php
                }
                ?>
                </table>
                <?php

                // Services
                if (!empty($store['services']))
                {
                ?>
                <h2><strong>Services Offered</strong></h2>
                <table class="bordered_outside">
                    <tr>
                        <td>
                    <?php
                    $ss = "";
                    $i = 0;
                    foreach ($store['services'] as $storeService)
                    {
                        $i++;
                        $ss .= $storeService['serviceName'] . ", ";
                    }
                    if ($i > 1)
                    {
                        $ss = substr($ss, 0, strlen($ss)-2);
                    }
                    echo $ss;
                    ?>
                        </td>
                    </tr>
                </table>
                <?php
                }

    /*
    if (!empty($hours))
    {
    ?>
            <table class='bordered_outside'>
            <?php
                echo $hours;
            ?>
            </table>
    <?php
    }
     */

            // Facebook (img link)
            if (!empty($store['facebookURL']))
            {
                ?>
                <p>
                    <a href="<?= $store['facebookURL']; ?>">
                        <img src="/css/images/items/facebook.jpg" class="no-border"/>
                    </a>
                </p>
                <?php
            }
    ?>

        </td>

        <!-- RIGHT COL -->
        <td>
            <?php
            // Owners
            if (!empty($store['ownerName']))
            {
                echo "<p><b>Owners: </b>" . $store['ownerName'] . "</p>";
            }

            // Bio
            if (!empty($store['bio']))
            {
                echo "<p>" . Utility::convertNLToBR($store['bio']) . "</p>";
            }

            // Store Images
            /*
            if (!empty($store['images']) && count($store['images']) == 1)
            {
                echo "<img style='width:585px;height:385px;'/>";
            }
            */
            ?>

        </td>
    </tr>

<?php
    // Map
    if (!empty($store['map']))
    {
        ?>
        <tr>
            <td colspan="2" class='center'>
                <a name='map'>
                <h2>Map and Directions</h2>
                </a>
                <!-- <img style="width:870px;height:550px;margin:0 auto;" src="" /> -->
                <?php
                echo html_entity_decode($store['map']);
                ?>
            </td>
        </tr>
        <?php
    }
    ?>

</table>