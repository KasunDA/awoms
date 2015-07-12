<?php
// Administrators Home
if ($_SESSION['user']['usergroup']['usergroupName'] == "Administrators")
{
?>
    <h1>Custom Admin Home Template</h1>

    <p>To modify this admin home page template, edit the file below:</p>
<?php
    echo "<p>".__FILE__."</p>";
}
// Store Owners Home
elseif ($_SESSION['user']['usergroup']['usergroupName'] == "Store Owners")
{
?>
    <h1>Store Owners Home</h1>
    
    <p>To modify this store owners home page template, edit the file below:</p>
<?php
    echo "<p>".__FILE__."</p>";
}
else
{
?>
    <h1>Welcome!</h1>

    <p>To modify this home page template, edit the file below:</p>
<?php
    echo "<p>".__FILE__."</p>";    
}
