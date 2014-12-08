<?php
// Load Cart Admin Header
require('admin_header.php');

// Show breadcrumbs if not in ajax
if (empty($_REQUEST['m'])) {
    ?>
    <div class="container">
        <ul class="breadcrumb">
            <li><a href="<?php echo cartPublicUrl; ?>admin/">Admin Home</a> <span class="divider">/</span></li>
            <?php
            if (!empty($_REQUEST['p'])) {
                echo "<li class='active'><a href='" . cartPublicUrl . "admin/?p=" . $_REQUEST['p'] . "'>" . ucwords(str_replace('_',
                                                                                                                                ' ',
                                                                                                                                $_REQUEST['p'])) . "</a></li>";
            }
            ?>
        </ul>
    </div>
    <?php
}

// Load selected action required file
if (isset($_REQUEST['p']) && $_REQUEST['p'] != 'index'
) {
    $p = $_REQUEST['p'];
    // Admin Getfile
    if ($p == 'getfile') {
        $p = 'admin_getfile';
    }
// Default view cart
} else {
    $p = 'admin';
}

//
// ACL Check
//
if (empty($_REQUEST['m'])) {
    // Page authorization check
    $access = false;
    switch ($p) {
        // Everyone allowed
        case "admin": {
                $access = true;
                break;
            }
        // Everyone allowed
        case "customer": {
                $access = true;
                break;
            }
        // Everyone allowed
        case "order": {
                $access = true;
                break;
            }
        // Global Read/Write only
        case "log": {
                if (!empty($globalACL['read'])) {
                    $access = true;
                }
                break;
            }
        // Cart/Global Read/Write only
        case "report": {
                if (!empty($cartACL['read']) || !empty($globalACL['read'])
                ) {
                    $access = true;
                }
                break;
            }
        // Cart/Global Read/Write only
        case "product": {
                if (!empty($cartACL['read']) || !empty($globalACL['read'])
                ) {
                    $access = true;
                }
                break;
            }
        // Cart/Global Read/Write only
        case "product_category": {
                if (!empty($cartACL['read']) || !empty($globalACL['read'])
                ) {
                    $access = true;
                }
                break;
            }
        // Cart/Global Read/Write only
        case "cart": {
                if (!empty($cartACL['read']) || !empty($globalACL['read'])
                ) {
                    $access = true;
                }
                break;
            }
        // Cart/Global Read/Write only
        // (Everyone allowed v1.0.1)
        case "user": {
                //if (!empty($cartACL['read'])
//                            || !empty($globalACL['read'])
//                    ) {
                $access = true;
//                }
                break;
            }
        // Cart/Global Read/Write only
        case "admin_getfile": {
                if (!empty($cartACL['read']) || !empty($globalACL['read'])
                ) {
                    $access = true;
                }
                break;
            }
        // Else = allow = 404 catch
        default: {
                $access = true;
                break;
            }
    }
// End ajax check
// @todo acl
} else {
    $access = true;
}

// 403
if ($access === FALSE) {
    ?>
    <div class="container">
        <h1>Unauthorized access <span>:(</span></h1>
        <p>Sorry, but you do not have permissions to the page you were trying to view.</p>
        <p>Please <a href="javascript:history.go(-1)">go back</a> and try again.</p>
    </div>
    <?php
// 404
} elseif ($access === TRUE && !is_file($p . '.php')
) {
    ?>
    <div class="container">
        <div class='span6 offset3'>
            <h1>404 Not found <span>:(</span></h1>
            <p>Sorry, but the page you were trying to view does not exist.</p>
            <p>It looks like this was the result of either:</p>
            <ul>
                <li>a mistyped address</li>
                <li>an out-of-date link</li>
            </ul>
            <p>Please <a href="javascript:history.go(-1)">go back</a> and try again.</p>
            <script>
                var GOOG_FIXURL_LANG = (navigator.language || '').slice(0, 2), GOOG_FIXURL_SITE = location.host;
            </script>
            <script src="https://linkhelp.clients.google.com/tbproxy/lh/wm/fixurl.js"></script>
        </div>
    </div>
    <?php
// Load file
} else {
    include($p . '.php'); // Show valid page
}

// Load Cart Admin Footer (if not in ajax mode)
if (empty($_REQUEST['m'])) {
    require('admin_footer.php');
}
?>