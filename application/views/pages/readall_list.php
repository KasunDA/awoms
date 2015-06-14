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
        <th>Edit</th>
        <?php
        if ($showBrandColumn)
        {
            echo "<th>Brand Name</th>";
        }
        ?>
        <th><?php echo trim(ucfirst($this->controller), "s"); ?> Private Name</th>
        <th><?php echo trim(ucfirst($this->controller), "s"); ?> Meta Title</th>
        <th><?php echo trim(ucfirst($this->controller), "s"); ?> Heading</th>
    </tr>

    <?php
    // Rows
    $lastBrandName = "";
    foreach ($items as $item)
    {
        $itemID      = $item[$lbl . 'ID'];
        $itemName    = $item[$lbl . 'PrivateName'];
        $itemMetaTitle    = $item[$lbl . 'MetaTitle'];
        $itemHeading    = $item[$lbl . 'Heading'];
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

        $rowData .= "<td><a href='" . $updateLink . "'>[Edit]</a></td>";

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
                <a href='" . $updateLink . "'>
                    " . $itemName . "
                </a>
            </td>
            <td>
                " . $itemMetaTitle . "
            </td>
            <td>
                <a href='" . $updateLink . "'>
                    " . $itemHeading . "
                </a>
            </td>
        </tr>";

        echo $rowData;
    }
    ?>
</table>