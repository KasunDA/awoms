<?php

// Security check: Deny direct file access; must be loaded through index getfile
if (count(get_included_files()) == 1) {
    header("Location: index.php");
    die("403");
}
//
// ACL Check
//
if (empty($cartACL['read']) && empty($globalACL['read'])
) {
    die("Unauthorized Access (403)");
}
?>
<?php

// List report options / header
include(cartPrivateDir . 'templates/'.$_SESSION['cartTheme'].'/admin/report/report_list.inc.phtml');

// Cart selected
if (isset($_REQUEST['cartID'])) {

    //
    // ACL Check ensuring access to selected cart
    //
    if (empty($globalACL['read']) && $_REQUEST['cartID'] != $_SESSION['cartID']) {
        die('403');
    }
    $cartID = $_REQUEST['cartID'];

// Default: All carts if global admin, otherwise admins cart
} else {

    if (!empty($globalACL['read'])) {
        $cartID = 'ALL';
    } else {
        $cartID = $_SESSION['cartID'];
    }
}

$report = new killerCart\Report();

// Daterange selected
if (isset($_REQUEST['dateRange'])) {
    $dateRange = $_REQUEST['dateRange'];
} else {
    $dateRange = '30';
}
$dateFrom = killerCart\Util::getPastDateTimeUTC($dateRange);

// Report selected
if (isset($_REQUEST['reportName'])) {
    $reportName = $_REQUEST['reportName'];
} else {
    $reportName = 'salesTotals';
}

//
// Generate report
//
if ($reportName == 'salesTotals') {
    $r           = $report->getSalesReportByStatus($cartID, $dateFrom, array('PD', 'CMP', 'ARC'));
    $reportLabel = '<h3>Sales Totals <small>since ' . $dateFrom . '</small></h3>';
    $reportData  = 1;
} elseif ($reportName == 'declinedOrders') {
    $r           = $report->getSalesReportByStatus($cartID, $dateFrom, array('DCL'));
    $reportLabel = '<h3>Declined Orders <small>since ' . $dateFrom . '</small></h3>';
    $reportData  = 1;
} elseif ($reportName == 'salesByProduct') {
    $r           = $report->getSalesReportGroupByItem($cartID, $dateFrom, array('PD', 'CMP', 'ARC'));
    $reportLabel = '<h3>Sales By Product <small>since ' . $dateFrom . '</small></h3>';
    $reportData  = 2;
}

// Report data (Chart formatted)
if ($reportData == 1) {
    $chartColumns  = "['Date', 'Order Price', 'Taxable Amount', 'Tax', 'Delivery'],";
    $chartData     = '';
    $orderTotal    = 0;
    $taxableTotal  = 0;
    $deliveryTotal = 0;
    $taxTotal      = 0;
    foreach ($r as $d) {
        $date     = killerCart\Util::getServerDateTimeFromUTCDateTime($d['dateOrderPlaced']);
        $order    = killerCart\Util::getFormattedNumber($d['totalOrderPrice']);
        $orderTotal += $order;
        $taxable  = killerCart\Util::getFormattedNumber($d['orderTaxableAmount']);
        $taxableTotal += $taxable;
        $delivery = killerCart\Util::getFormattedNumber($d['totalOrderDelivery']);
        $deliveryTotal += $delivery;
        $tax      = killerCart\Util::getFormattedNumber($d['totalOrderTax']);
        $taxTotal += $tax;
        $chartData .= "['" . $date . "', " . $order . ", " . $taxable . ", " . $tax . ", " . $delivery . "],";
    }
    // Show report
    include(cartPrivateDir . 'templates/'.$_SESSION['cartTheme'].'/admin/report/report_sales_totals.inc.phtml');
} elseif ($reportData == 2) {
    $chartColumns = "['ProductID', 'Total Sales', 'Quantity'],";
    $chartData    = '';
    $tableData    = '';
    $salesTotal   = 0;
    $qtyTotal     = 0;
    foreach ($r as $d) {
        //$productID = $d['productID'];
        $productName = $d['productName'];
        $sales       = killerCart\Util::getFormattedNumber($d['productTotal']);
        $salesTotal += $sales;
        $qty         = $d['quantity'];
        $qtyTotal += $qty;
        $chartData .= "['" . $productName . "', " . $sales . ", " . $qty . "],";
        $tableData .= "<tr><td>" . $productName . "</td><td>" . $qty . "</td><td>" . $sales . "</td></tr>";
    }
    // Show report
    include(cartPrivateDir . 'templates/'.$_SESSION['cartTheme'].'/admin/report/report_sales_by_item.inc.phtml');
}
?>