<?php
// Create Button
if ($showCreateButton)
{
    ?>
    <button type='button' class="button_save" onClick="location.href = '/<?php echo $this->controller . "/create"; ?>';">
        Add <?php echo $label; ?>
    </button>
    <?php
}

// No Items?
if (empty($items))
{
    echo "<div class='alert warning'>Sorry! There are no " . $label . "s yet.</div>";
    return;
}
?>

<table class="bordered">
    <tr>
        <?php
        if ($showBrandColumn)
        {
            echo "
                <th>Brand Name</th>
                <th>Store Number</th>";
        }
        ?>
        <th><?php echo $label; ?> Name</th>
    </tr>

    <?php
    $lastBrandName = "";
    foreach ($items as $item)
    {
        $itemID      = $item[$lbl . 'ID'];
        $itemName    = $item[$lbl . 'Name'];
        $itemNameSEO = str_replace(' ', '-', $itemName);
        $updateLink  = BRAND_URL . $this->controller . '/update/' . $itemID . '/' . $itemNameSEO;

        $rowData = "<tr>";

        if ($showBrandColumn)
        {
            $rowData .= "<td>";
            foreach ($brands as $brand)
            {
                if ($item['cartID'] == $brand['cartID'])
                {
                    if ($lastBrandName != $brand['brandName'])
                    {
                        $rowData .= $brand['brandName'];
                    }
                    else
                    {
                        $rowData .= "&nbsp;";
                    }
                    break;
                }
            }
            $rowData .= "</td>";

            $lastBrandName = $brand['brandName'];

            if ($showStoreColumn)
            {
                $rowData .= "<td>";
                foreach ($stores as $store)
                {
                    if ($item['cartID'] == $store['cartID'])
                    {
                        $storeNameSEO = str_replace(' ', '-', $store['storeNumber']);
                        $storeLink    = BRAND_URL . 'stores/update/' . $item['storeID'] . "/" . $storeNameSEO;
                        $rowData .= "<a href='" . $storeLink . "'>" . $storeNameSEO . "</a>";
                        break;
                    }
                }
                $rowData .= "</td>";
            }
            
        }

        $rowData .= "
            <td>
                <a href='" . $updateLink . "'>
                    " . $itemName . "
                </a>
            </td>

        </tr>";
        echo $rowData;
    }
    ?>
</table>
