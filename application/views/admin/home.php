<?php
// Administrators Home
if ($_SESSION['user']['usergroup']['usergroupName'] == "Administrators")
{
    echo "Welcome Administrator!";
}
// Store Owners Home
elseif ($_SESSION['user']['usergroup']['usergroupName'] == "Store Owners")
{
    
    $Menu = new Menu();
    
    $menuType             = "Body";
    $menuName             = "Your Stores";
    $menuUlClass          = "menu";
    $menuTitle            = $menuName;
    $Menus['your_store'] = $Menu->getMenu($menuType, $menuName, $menuUlClass, $menuTitle);
    
    $menuType             = "Body";
    $menuName             = "Products";
    $menuUlClass          = "menu";
    $menuTitle            = $menuName;
    $Menus['products'] = $Menu->getMenu($menuType, $menuName, $menuUlClass, $menuTitle);
    
    $menuType             = "Body";
    $menuName             = "Training Info";
    $menuUlClass          = "menu";
    $menuTitle            = $menuName;
    $Menus['training_info'] = $Menu->getMenu($menuType, $menuName, $menuUlClass, $menuTitle);
    
    $menuType             = "Body";
    $menuName             = "Advertising";
    $menuUlClass          = "menu";
    $menuTitle            = $menuName;
    $Menus['advertising'] = $Menu->getMenu($menuType, $menuName, $menuUlClass, $menuTitle);
?>
    
<span class="header-text"><?php echo BRAND; ?> Owners Section</span>
    <table>
        <tr>
            <td width="50">
            </td>
            
            <td>
                <table>
                    <tr>
                        <td width="125">
                            <?php
                                echo $Menus['your_store'];
                                echo $Menus['products'];
                            ?>
                        </td>
                        <td width="150">
                            <?php
                                echo $Menus['training_info'];
                                echo $Menus['advertising'];
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <p>
                                <strong>NOTE:</strong> None of the images on this website are for printing purposes. If you need an image for any marketing purposes at all please contact <a href="mailto:ads@hutno8.com">ads@hutno8.com</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
            
            <td width="400">
                <img src="https://www.hutno8.com/images/logo2.png" width="346" style="border: none;"/>
            </td>
            
        </tr>
    </table>
    
<?php
}
else
{
    echo "Welcome user!";
}
