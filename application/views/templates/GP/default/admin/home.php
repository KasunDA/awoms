<?php
// Administrators Home
if ($_SESSION['user']['usergroup']['usergroupName'] == "Administrators")
{
?>
    <h1>Custom Admin Home Template</h1>

    <p>To modify this admin page template, edit the file below:</p>
<?php
    echo "<p>".__FILE__."</p>";


}
// Store Owners Home
elseif ($_SESSION['user']['usergroup']['usergroupName'] == "Store Owners")
{

    // Define all menus to be loaded here
    $Menu = new Menu();
    $menuType             = "Body";
    $menuUlClass          = "menu";

    $menuName             = "Your Stores";
    $Menus['your_store'] = $Menu->getMenu($menuType, $menuName, $menuUlClass);

    $menuName             = "Supplemental Income";
    $Menus['supplemental_income'] = $Menu->getMenu($menuType, $menuName, $menuUlClass);

    $menuName             = "Misc Resources";
    $Menus['misc_resources'] = $Menu->getMenu($menuType, $menuName, $menuUlClass);

    $menuName             = "New Store Training Info";
    $Menus['training_info'] = $Menu->getMenu($menuType, $menuName, $menuUlClass);

    $menuName             = "Downloads";
    $Menus['downloads'] = $Menu->getMenu($menuType, $menuName, $menuUlClass);

    $menuName             = "Contacts";
    $Menus['contacts'] = $Menu->getMenu($menuType, $menuName, $menuUlClass);

    $menuName             = "Advertising";
    $Menus['advertising'] = $Menu->getMenu($menuType, $menuName, $menuUlClass);

    $menuName             = "Owners Store";
    $Menus['owners_store'] = $Menu->getMenu($menuType, $menuName, $menuUlClass);
?>
    
<span class="header-text"><?php echo BRAND; ?> Owners Section</span>
<h3 style="color:red;">Referrals. Get $3,000 CASH instantly!</h3>

    <table width="100%">
        <tr>
            <td width="33%">
                <?php
                    echo $Menus['your_store'];
                    echo $Menus['supplemental_income'];
                    echo $Menus['misc_resources'];
                ?>
            </td>
            <td width="33%">
                <?php
                    echo $Menus['training_info'];
                    echo $Menus['downloads'];
                    echo $Menus['contacts'];
                ?>
            </td>
            <td width="33%">
                <?php
                    echo $Menus['advertising'];
                    echo $Menus['owners_store'];
                ?>
            </td>
        </tr>
    </table>

<?php
}
else
{
    echo "Welcome user!";
}
