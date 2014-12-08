<div style="border: 3px solid red;">
    Missing files:
    <ul>
    <?php
    if (!is_file($dbFile))
    {
        echo "<li>".$dbFile."</li>";
    }

    if (!is_file($customConfigFile))
    {
        echo "<li>".$customConfigFile."</li>";
    }
    ?>
    </ul>

    <p>
        Please see the <b>README</b> file <b>Installation</b> instructions below.
    </p>
</div>
<?php
require(ROOT . DS . 'config' . DS . 'parsedown.php');
$Parsedown = new Parsedown();
echo $Parsedown->text(file_get_contents(ROOT . DS . 'README.md'));