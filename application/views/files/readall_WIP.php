<h1>File Management</h1>

<a href="/filemanager/dialog.php?type=0" class="btn iframe-btn" type="button">Open Filemanager</a>

<?php
$pageJavaScript[] = "
    $('.iframe-btn').fancybox({
        'width'		: 900,
        'height'	: 600,
        'type'		: 'iframe',
        'autoSize'  : false
    });";
?>


<!--[if lt IE 9]>
<script src = "http://ie7-js.googlecode.com/svn/version/2.1(beta4)/IE9.js"></script>
<![endif]-->

<style>
    /* Minimal css for clickable pure CSS collapsible tree menu */
    /* As specific as possible to prevent interference with other code */

    #menutree li {
        list-style: none;          /* all list item li dots invisible */
    }

    li .menu_label + input[type=checkbox] {
        opacity: 0;             /* checkboxes invisible and use no space */
    }                        /* display: none; is better but fails in ie8 */

    li .menu_label {
        cursor: pointer;        /* cursor changes when you mouse over this class */
    }                         /* could add the many user-select: none; commands here */

    li .menu_label + input[type=checkbox] + ol > li
    {
        display: none;         /* prevents sublists below unchecked labels from displaying */
    }

    li .menu_label + input[type=checkbox]:checked + ol > li
    {
        display: block;         /* display submenu on click */
    }
</style>

<?php
$fileID           = empty($_GET['fileID']) ? "DEFAULT" : $_GET['fileID'];
$brandID          = empty($_GET['brandID']) ? "NULL" : $_GET['brandID'];
$storeID          = empty($_GET['storeID']) ? "NULL" : $_GET['storeID'];
$categoryID       = empty($_GET['categoryID']) ? "NULL" : $_GET['categoryID'];
$productID        = empty($_GET['productID']) ? "NULL" : $_GET['productID'];
$userID           = empty($_GET['userID']) ? "NULL" : $_GET['userID'];
$usergroupID      = empty($_GET['usergroupID']) ? "NULL" : $_GET['usergroupID'];
$customerID       = empty($_GET['customerID']) ? "NULL" : $_GET['customerID'];
?>

<table>
    <tr>
        <td>

            <?php
// Step 1: Brands level
            if ($brandID == "NULL")
            {
                ?>

                <table class="bordered">
                    <tr>
                        <th>Brand</th>
                    </tr>

                    <?php
                    $Brand  = new Brand();
                    $brands = $Brand->getWhere();

                    foreach ($brands as $brand)
                    {
                        $link = "/files/readall/?brandID=" . $brand['brandID'];
                        echo "<tr><td><a href='" . $link . "'>" . $brand['brandName'] . "</a></td></tr>";
                    }

                    ?>
                </table>

                <?php
            }
            else
            {

                $File  = new File();
                $files = $File->getWhere(array('brandID' => $brandID));
                ?>

                <table class="bordered">
                    <tr>
                        <th>Brand's Files</th>
                    </tr>

                    <tr>
                        <td>
                            <?php
                            // @TODO Nested levels... (like Menu...)
                            #// Brands
                            #if (ACL::IsUserAuthorized('brands', 'readall'))
                            #{
//            if (ACL::IsUserAuthorized('brands', 'create'))
//            {
//                $tree['Brands']['sub']['Add Brand'] = array(
//                    "display" => "Add Brand",
//                    "url"     => "/brands/create");
//            }
                            #}
                            /** End construct tree based off ACL * */
                            /** Build Tree based on ACL; so that only menu items showing are those the user has access to * */
                            $tree  = array();
                            foreach ($files as $file)
                            {
                                $parentID        = empty($file['parentFileID']) ? $file['fileID'] : $file['parentFileID'];
                                $tree[$parentID] = array(
                                    "id"      => $file['fileID'],
                                    "display" => "(" . $file['fileID'] . ") " . $file['displayName'],
                                    "url"     => "/files/update/" . $file['fileID']);
                            }
                            $File      = new File();
                            $finalTree = $File::buildTree($tree);
                            echo $finalTree;
                            var_dump($finalTree);
                            ?>
                        </td>
                    </tr>
                </table>


                <?php
            }
            ?>

        </td>
        <td>

            <?php
// Step 1: Store level
            if ($storeID == "NULL")
            {
                ?>

                <table class="bordered">
                    <tr>
                        <th>Store</th>
                    </tr>

                    <?php
                    $Store  = new Store();
                    $stores = $Store->getWhere();

                    foreach ($stores as $store)
                    {
                        $link = "/files/readall/?storeID=" . $store['storeID'];
                        echo "<tr><td><a href='" . $link . "'>" . $store['storeNumber'] . "</a></td></tr>";
                    }
                    ?>
                </table>

                <?php
            }
            else
            {

                $File  = new File();
                $files = $File->getWhere(array('storeID' => $storeID));
                ?>

                <table class="bordered">
                    <tr>
                        <th>Store's Files</th>
                    </tr>

                    <?php
                    foreach ($files as $file)
                    {
                        echo "<tr><td><a href='/files/update/" . $file['fileID'] . "'>[Update] " . $file['displayName'] . "</a></td></tr>";
                    }
                    ?>
                </table>


                <?php
            }
            ?>
        </td>



        <td>

            <?php
// Step 1: User level
            if ($userID == "NULL")
            {
                ?>

                <table class="bordered">
                    <tr>
                        <th>User</th>
                    </tr>

                    <?php
                    $User  = new User();
                    $users = $User->getWhere();

                    foreach ($users as $user)
                    {
                        $link = "/files/readall/?userID=" . $user['userID'];
                        echo "<tr><td><a href='" . $link . "'>" . $user['userName'] . "</a></td></tr>";
                    }
                    ?>
                </table>

                <?php
            }
            else
            {

                $File  = new File();
                $files = $File->getWhere(array('userID' => $userID));
                ?>

                <table class="bordered">
                    <tr>
                        <th>User's Files</th>
                    </tr>

                    <?php
                    foreach ($files as $file)
                    {
                        echo "<tr><td><a href='/files/update/" . $file['fileID'] . "'>[Update] " . $file['displayName'] . "</a></td></tr>";
                    }
                    ?>
                </table>


                <?php
            }
            ?>
        </td>

    </tr>
</table>

<?php
// Upload Form
$fileType = "image";
include('createForm.php');
