<?php
foreach ($menus as $menu)
{
?>

    <table>
        <tr>
            <th></th>
            <th>Display</th>
            <th>URL</th>
        </tr>
    
<?php
    for ($i=0; $i<count($menu); $i++)
    {
        if ($i == 0)
        {
            $menuID = $menu[$i]['menuID'];
        }
?>
    
        <tr>
            <td>
<?php
    // Actions
echo "
    <button type='button' id='openModalEditMenus' name='frmEditMenus' value='".$menu[$i]['linkID']."' class='openModal'>Edit</button>
    &nbsp;
    <button value='".$menu[$i]['linkID']."'>Delete</button>&nbsp;";


?>
            </td>
            
            <td>
<?php
    echo $menu[$i]['display'];
?>
            </td>
            
            <td>
<?php
    echo $menu[$i]['url'];
?>                
            </td>
        </tr>
    
<?php
    }
?>
    </table>
<?php
}
?>
    

