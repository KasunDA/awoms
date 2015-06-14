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
        // (@TODO: if user owns multiple stores?)
//        if ($showStoreColumn)
//        {
//            echo "<th>Store Number</th>";
//        }
        if ($showUsergroupColumn)
        {
            echo "<th>Group</th>";
        }
        ?>
        <th>Username</th>
        <th>Full Name</th>
    </tr>

    <?php
    // Rows
    $lastBrandName = "";
    foreach ($items as $item)
    {
        $itemID      = $item[$lbl . 'ID'];
        $itemName    = $item[$lbl . 'Name'];
        $itemNameSEO = str_replace(' ', '-', $itemName);
        $updateLink  = BRAND_URL . $this->controller . '/update/' . $itemID . '/' . $itemNameSEO;

        $rowData = "<tr>";

        // Brand column
        if ($showBrandColumn)
        {
            $rowData .= "<td>";
            foreach ($usergroups as $usergroup)
            {
                if ($item['usergroupID'] == $usergroup['usergroupID'])
                {
                    foreach ($brands as $brand)
                    {
                        if ($usergroup['brandID'] == $brand['brandID'])
                        {
                            if ($brand['brandName'] == $lastBrandName)
                            {
                                $rowData .= "";
                            }
                            else
                            {
                                $rowData .= $brand['brandName'];
                            }
                            break;
                        }
                    }
                }
            }
            $lastBrandName = $brand['brandName'];
            $rowData .= "</td>";
        }

        // Store column (@TODO: if user owns multiple stores?)
//        if ($showStoreColumn)
//        {
//            $rowData .= "<td>";
//            foreach ($usergroups as $usergroup)
//            {
//                if ($item['usergroupID'] == $usergroup['usergroupID'])
//                {
//                    foreach ($stores as $store)
//                    {
//                        if ($usergroup['storeID'] == $store['storeID'])
//                        {
//                            $rowData .= $store['storeNumber'];
//                            break;
//                        }
//                    }
//                }
//            }
//            $rowData .= "</td>";
//        }
        // Usergroup column
        if ($showUsergroupColumn)
        {
            $rowData .= "<td>";
            foreach ($usergroups as $usergroup)
            {
                if ($item['usergroupID'] == $usergroup['usergroupID'])
                {
                    $rowData .= $usergroup['usergroupName'];
                    break;
                }
            }
            $rowData .= "</td>";
        }


        $rowData .= "
            <td>
                <a href='" . $updateLink . "'>
                    " . $item['userName'] . "
                </a>
            </td>

            <td>";
        if (!empty($item['lastName']) || !empty($item['firstName']))
        {
            $rowData .= "
                    <a href='" . $updateLink . "'>
                        " . $item['lastName'] . ", " . $item['firstName'] . "
                    </a>";
        }
        $rowData .= "
        </td>
    </tr>";
        echo $rowData;
    }
    ?>
</table>
