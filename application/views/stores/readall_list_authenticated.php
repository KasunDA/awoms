<h1>Your Stores</h1>

<table class="bordered">
    <tr>
        <?php
        if ($showBrandColumn)
        {
            echo "<th>Brand Name</th>";
        }
        ?>
        <th>Store Number</th>
        <th>State</th>
        <th>City</th>
        <th>Coding&nbsp;</th>
        <th>Phone</th>
    </tr>

    <?php
    /* Store Rows */
    $lastBrandName = "";
    foreach ($items as $item)
    {
        $itemID      = $item['storeID'];
        $storeNumber = $item['storeNumber'];
        $storePhone  = $item['phone'];
        $storeEmail  = $item['email'];

        // Address
        if (!empty($item['address']))
        {
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
        }
        else
        {
            $storeAddress = "";
            $storeState   = "";
            $storeCity    = "";
        }

        $updateLink = BRAND_URL . 'stores/update/' . $itemID . '/' . $storeState . '/' . $storeCity . '/' . $storeNumber;

        // Coding
        switch ($item['coding'])
        {
            case "N":
                $activeClass = "warning";
                break;
            case "T":
            case "O/C":
                $activeClass = "info";
                break;
            case "C":
                $activeClass = "failure";
                break;
            case "O":
                $activeClass = "success";
                break;
            default:
                $activeClass = "failure";
                break;
        }
        $storeCoding = "<div class='alert " . $activeClass . " no-img center'>" . $item['coding'] . "</div>";

        $rowData = "<tr>";

        if ($showBrandColumn)
        {
            foreach ($brands as $brand)
            {
                if ($item['brandID'] == $brand['brandID'])
                {
                    if ($lastBrandName == $brand['brandName'])
                    {
                        $rowData .= "<td>&nbsp;</td>";
                    }
                    else
                    {
                        $rowData .= "<td>" . $brand['brandName'] . "</td>";
                    }
                    break;
                }
            }
            $lastBrandName = $brand['brandName'];
        }

        $rowData .= "
        <td>
            <a href='" . $updateLink . "'>" . $storeNumber . "</a>
        </td>
        <td>
            " . $storeState . "
        </td>
        <td>
            <a href='" . $updateLink . "'>" . $storeCity . "</a>
        </td>
        <td>
            " . $storeCoding . "
        </td>
        <td>
            " . $storePhone . "
        </td>
    </tr>";

        echo $rowData;
    }
    ?>
</table>
