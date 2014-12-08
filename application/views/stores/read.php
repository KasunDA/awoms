
<?php
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

// Brand
$Brand = new Brand();
$brand = $Brand->getSingle(array('brandID' => $store['brandID']));
?>

<table style="width:100%; margin-left:30px">
    <tr>
        <td style="width:300px;">

            <header>
                <h1>Locations &amp; Services</h1>
                <h2><?= $brand['brandName'] . ' ' . $storeCity; ?></h2>
            </header>

            <table class="bordered_outside">
                <tr>
                    <td><strong>Address</strong></td>
                    <td>
                        <?= $storeAddress; ?>
                    </td>
                </tr>

                <?php
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
                ?>
            </table>

            <?php
            // Hours
            $days  = array('hrsMon' => 'Monday',
                'hrsTue' => 'Tuesday',
                'hrsWed' => 'Wednesday',
                'hrsThu' => 'Thursday',
                'hrsFri' => 'Friday',
                'hrsSat' => 'Saturday',
                'hrsSun' => 'Sunday');
            $hours = "<table>";
            foreach ($days as $code => $codeLabel)
            {
                $hours .= "<tr><td>" . $codeLabel . "</td><td>" . $store[$code] . "</td></tr>";
            }
            $hours .= "</table>";
            ?>
            <h2><strong>Hours</strong></h2>
            <?= $hours; ?>

            <?php
            // Facebook
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
        <td>

            <?php
            // Bio
            if (!empty($store['bio']))
            {
                echo "<p>" . $store['bio'] . "</p>";
            }

            if (!empty($store['images']) && count($store['images']) == 1)
            {
                /* Display Multiple Store Images */
                echo "<img style='width:585px;height:385px;'/>";
            }
            ?>

        </td>
    </tr>

    <?php
    if (!empty($store['services']))
    {
        ?>
        <tr>
            <td colspan="2">
                <h2><strong>Services Offered</strong></h2>
                <ul>
                    <?php
                    foreach ($store['services'] as $storeService)
                    {
                        echo "<li>" . $storeService['serviceName'] . "</li>";
                    }
                    ?>
                </ul>
            </td>
        </tr>
        <?php
    }

    if (!empty($store['images']) && count($store['images']) > 1)
    {
        /* Display Multiple Store Images */
        $cols  = count($store['images']);
        $table = "<table>";
        $colOn = 1;
        $i     = 0;
        foreach ($store['images'] as $storeImage)
        {
            $showImage = "<img src='" . $storeImage . "' style='width:300px;height:300px;'/>";

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
        echo $table;
    }

    if (!empty($store['map']))
    {
        ?>
        <tr>
            <td colspan="2">
                <h2>Map</h2>
                <img style="width:870px;height:550px;margin:0 auto;" />
            </td>
        </tr>
        <?php
    }
    ?>

</table>