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
            echo "<th>Brand Name</th>";
        }
        ?>
        <th><?php echo $this->model; ?> Name</th>
    </tr>

    <?php
    // Rows
    foreach ($items as $item)
    {
        $itemID      = $item[$lbl . 'ID'];
        $itemName    = $item[$lbl . 'Name'];
        $itemNameSEO = str_replace(' ', '-', $itemName);
        $updateLink  = BRAND_URL . $this->controller . '/update/' . $itemID . '/' . $itemNameSEO;

//        $activeClass = "success";
//        $activeLabel = "Y";
//        if ($item[$lbl . 'Active'] == 0)
//        {
//            $activeClass = "failure";
//            $activeLabel = "N";
//        }

        $rowData = "<tr>";
        //$rowData .= "<td><div class='alert " . $activeClass . " no-img center'>" . $activeLabel . "</div></td>";

        if ($showBrandColumn)
        {
            foreach ($brands as $brand)
            {
                if ($item['brandID'] == $brand['brandID'])
                {
                    $rowData .= "<td>" . $brand['brandName'] . "</td>";
                    break;
                }
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