<table>
    <tr>
        <th>Active&nbsp;</th>
        <?php
// Add Brand Column if Admin
        if ($_SESSION['user']['usergroup']['usergroupName'] == "Administrators") {
            echo "<th>&nbsp;Brand Name</th>";
        }
        ?>
        <th>Menu Name</th>
    </tr>

    <?php
    $Brand = new Brand();
    foreach ($menus as $m) {
        $activeClass = "success";
        $activeLabel = "Y";
        if ($m['menuActive'] == 0) {
            $activeClass = "failure";
            $activeLabel = "N";
        }
        echo "
        <tr>
            <td><div class='alert " . $activeClass . " no-img center'>" . $activeLabel . "</div></td>
        ";

        // Add Brand Column if Admin
        if ($_SESSION['user']['usergroup']['usergroupName'] == "Administrators") {
            $b = $Brand->getWhere(array('brandID' => $m['brandID']));
            echo "<td>&nbsp;" . $b['brandName'] . "</td>";
        }

        echo "<td><a href='/menus/update/" . $m['menuID'] . "'>" . $m['menuName'] . "</a></td>
        </tr>";
    }
    ?>
</table>
