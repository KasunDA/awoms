<?php
$table = "
    <table>
        <tr>
            <th>Brand</th>
            <th>Group/User</th>
            <th>Controller</th>
            <th>Create</th>
            <th>Read</th>
            <th>Update</th>
            <th>Delete</th>
        </tr>
    ";

$Brand  = new Brand();
$brands = $Brand->getWhere();

$Usergroup  = new Usergroup();
$usergroups = $Usergroup->getWhere();

$User  = new User();
$users = $User->getWhere();

foreach ($items as $item)
{
    $table .= "<tr>";

    // Brand
    if (!empty($item['brandID']))
    {
        foreach ($brands as $brand)
        {
            if ($brand['brandID'] == $item['brandID'])
            {
                break;
            }
        }
    }

    $who = NULL;

    // Group
    if (!empty($item['usergroupID']))
    {
        foreach ($usergroups as $usergroup)
        {
            if ($usergroup['usergroupID'] == $item['usergroupID'])
            {
                break;
            }
        }
        $who = $usergroup['usergroupName'];
    }

    // User
    if (!empty($item['userID']))
    {
        foreach ($users as $user)
        {
            if ($user['userID'] == $item['userID'])
            {
                break;
            }
        }
        $who = $user['userName'];
    }

    $table .= "<td>" . $brand['brandName'] . "</td>";

    $table .= "<td>" . $who . "</td>";

    $table .= "<td>" . ucwords($item['controller']) . "</td>";

    $table .= "<td>";
    if (!empty($item['create']))
    {
        $table .= "Yes";
    }
    else
    {
        $table .= "No";
    }
    $table .= "</td>";

    $table .= "<td>";
    if (!empty($item['read']))
    {
        $table .= "Yes";
    }
    else
    {
        $table .= "No";
    }
    $table .= "</td>";

    $table .= "<td>";
    if (!empty($item['update']))
    {
        $table .= "Yes";
    }
    else
    {
        $table .= "No";
    }
    $table .= "</td>";

    $table .= "<td>";
    if (!empty($item['delete']))
    {
        $table .= "Yes";
    }
    else
    {
        $table .= "No";
    }
    $table .= "</td>";

    $table .= "</tr>";
}

$table .= "</table>";
?>

<h1>Access Control List</h1>
<?= $table; ?>