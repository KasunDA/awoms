<?php

// Security check: Deny direct file access; must be loaded through index getfile
if (count(get_included_files()) == 1) {
    header("Location: index.php");
    die("403");
}
?>
<?php

//
// ACL Check
//
if (empty($_SESSION['user']['ACL']['cart']['read']) && empty($_SESSION['user']['ACL']['global']['read'])
) {
    die("Unauthorized Access (403)");
}
?>
<?php

//
// Export
//
if (!empty($_REQUEST['a']) && $_REQUEST['a'] == 'export'
) {

    // Selected type
    if (!empty($_REQUEST['type'])) {
        $tables = $_REQUEST['type'];
    } else {
        $tables = '';
    }

    // Connect to Database
    $db      = killerCart\new \Database();
    $time    = microtime(true);
    $outfile = cartPrivateDir . 'db.out.' . $time . '.txt';
    $done    = FALSE;
    // Test ability to use mysqldump
    if (function_exists('exec')) {
        exec("/usr/bin/mysqldump --user='" . USER . "' --password='" . PASS . "' --host='" . HOST . "' --port=" . PORT . " " . DBNAME . " " . $tables . " > '" . $outfile . "'",
             $o, $e);
        if (!empty($e)) {
            #echo '<h4>mysqldump failed</h4>';
            #var_dump($o, $e);
        } else {
            $done = TRUE;
        }
    }

    // Test ability to use FILE privilege
    if ($done === FALSE && $tables != '') { // Can try for single tables
        //echo '<h4>Testing MySQL FILE privilege...</h4>';
        $q = "SELECT * INTO OUTFILE '" . $outfile . "' FROM " . $tables;
        $r = $db->query($q);
        if (isset($r)) {
            $done = TRUE;
            #echo '<h4>FILE successful</h4>';
            #var_dump($r);
        } else {
            #echo '<h4>No FILE permissions...</h4>';
            #var_dump($r);
        }
    }

    if ($done === TRUE) {
        #echo '<h4>Export completed!</h4>';
        $text = file_get_contents($outfile);
        if ($tables == '') {
            $label = 'FULL';
        } else {
            $label = $tables;
        }

        $msg = '(' . $_SESSION['user']['userID'] . ') ' . $_SESSION['user']['userName'] . ' requested export of ' . $label . ' successfully';
        \Errors::debugLogger($msg);
        $e   = new \Errors();
        $e->dbLogger($msg, $_SESSION['cartID'], 'Audit', __FILE__, __LINE__);
        header("Content-Disposition: attachment; filename=\"export." . $label . "." . $time . ".sql.txt\"");
        echo $text;
    } else {
        echo '<h4>Unable to export data through portal. Please use MySQL tools to export your database manually.</h4>';
    }
}
?>