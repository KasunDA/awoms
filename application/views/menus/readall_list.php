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
        if ($isGlobalAdmin)
        {
            echo "<th>Brand Name</th>";
        }
        ?>
        <th>Menu Type</th>
        <th>Menu Name</th>
        <th>Login Restricted</th>
    </tr>

    <?php
    for ($i = 0; $i < count($menus); $i++)
    {
        $updateLink = BRAND_URL . 'menus/update/' . $menus[$i]['menuID'] . '/' . $menus[$i]['menuName'] . '/' . str_replace(' ', '-',
                                                                                                                            $menus[$i]['menuName']);
        echo "<tr>";

        if ($isGlobalAdmin)
        {
            foreach ($brands as $brand)
            {
                if ($brand['brandID'] == $menus[$i]['brandID'])
                {
                    break;
                }
            }

            echo "<td>" . $brand['brandName'] . "</td>";
        }
        ?>

        <td>
            <?php echo $menus[$i]['menuType']; ?>
        </td>

        <td>
            <a href="<?= $updateLink; ?>">
                <?php echo $menus[$i]['menuName']; ?>
            </a>
        </td>

        <td>
            <?php
            if (!empty($menus[$i]['menuRestricted']))
            {
                echo "<div class='alert success no-img center'>Y</div>";
            }
            else
            {
                echo "<div class='alert error no-img center'>N</div>";
            }
            ?>
        </td>

    </tr>

    <?php
}
?>
</table>
