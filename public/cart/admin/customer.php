<?php
// Security check: Deny direct file access; must be loaded through index getfile
if (count(get_included_files()) == 1) {
    header("Location: index.php");
    die("403");
}
?>
<?php
//
// Ajax Requests (POST only)
//
if (isset($_POST['m']) && $_POST['m'] == 'ajax'
) {

    //
    // Customer Edit Modal, Get Info
    //
    if (!empty($_POST['a']) && $_POST['a'] == 'getCustomerInfo'
    ) {

        \Errors::debugLogger(__FILE__ . __LINE__);
        // Sanitize Post
        $s          = new killerCart\Sanitize();
        $customerID = $s->filterSingle($_POST['customerID'], FILTER_SANITIZE_NUMBER_INT);

        // Load Customer Info
        $customer = new killerCart\Customer();
        $c        = $customer->getCustomerInfo($customerID);

        // Customer Edit Form
        include(cartPrivateDir . 'templates/' . $_SESSION['cartTheme'] . '/admin/customer/customer_edit_form.inc.phtml');
    }
    //
    // Customer Edit Modal, Save Info
    //
    elseif (!empty($_POST['a']) && $_POST['a'] == 'saveCustomerInfo'
    ) {

        \Errors::debugLogger(__FILE__ . __LINE__);
        // Sanitize Post
        $s    = new killerCart\Sanitize();
        $args = array('customerID'   => FILTER_SANITIZE_NUMBER_INT,
            'companyName'  => FILTER_SANITIZE_SPECIAL_CHARS,
            'firstName'    => FILTER_SANITIZE_SPECIAL_CHARS,
            'middleName'   => FILTER_SANITIZE_SPECIAL_CHARS,
            'lastName'     => FILTER_SANITIZE_SPECIAL_CHARS,
            'phone'        => FILTER_SANITIZE_STRING,
            'email'        => FILTER_SANITIZE_STRING,
            'notes'        => FILTER_SANITIZE_SPECIAL_CHARS,
            'username'     => FILTER_SANITIZE_SPECIAL_CHARS,
            'passphrase'   => FILTER_SANITIZE_SPECIAL_CHARS,
            'loginAllowed' => FILTER_SANITIZE_NUMBER_INT);
        $san  = $s->filterArray(INPUT_POST, $args);

        // Save New Customer Info
        $customer = new killerCart\Customer();
        $r        = $customer->updateCustomerInfo($san['customerID'], $san['companyName'], $san['firstName'], $san['middleName'],
                                                  $san['lastName'], $san['phone'], $san['email'], $san['notes'], $san['username'],
                                                  $san['passphrase'], $san['loginAllowed']);
        $c        = $customer->getCustomerInfo($san['customerID']);

        // Customer Edit Form
        include(cartPrivateDir . 'templates/' . $_SESSION['cartTheme'] . '/admin/customer/customer_edit_form.inc.phtml');
    }
    //
    // New Customer Modal, Create Customer
    //
    elseif (!empty($_POST['a']) && $_POST['a'] == 'createCustomer'
    ) {

        \Errors::debugLogger(__FILE__ . __LINE__);
        // Sanitize Post
        $s    = new killerCart\Sanitize();
        $args = array('cartID'      => FILTER_SANITIZE_NUMBER_INT,
            'groupID'      => FILTER_SANITIZE_NUMBER_INT,
            'firstName'    => FILTER_SANITIZE_SPECIAL_CHARS,
            'lastName'     => FILTER_SANITIZE_SPECIAL_CHARS,
            'email'        => FILTER_VALIDATE_EMAIL,
            'notes'        => FILTER_SANITIZE_SPECIAL_CHARS,
            'username'     => FILTER_SANITIZE_SPECIAL_CHARS,
            'passphrase'   => FILTER_SANITIZE_SPECIAL_CHARS,
            'loginAllowed' => FILTER_SANITIZE_NUMBER_INT);
        $san  = $s->filterArray(INPUT_POST, $args);

        // Create New Customer
        $customer   = new killerCart\Customer();
        $customerID = $customer->createCustomer($san['cartID'], $san['groupID'], $san['firstName'], $san['lastName'], $san['email'],
                                                $san['username'], $san['passphrase'], $san['notes'], $san['loginAllowed']);
        if (!empty($customerID)) {
            ?>
            <div class="alert alert-block alert-success span4">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <h4><i class="icon-ok"></i>&nbsp;Success!</h4>
                Customer has been created successfully! Customer ID: <strong><?php echo $customerID; ?></strong>
            </div>
            <?php
        } else {
            ?>
            <div class="alert alert-block alert-error span4">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <h4><i class="icon-ok"></i>&nbsp;Error!</h4>
                Sorry, we were unable to create your customer. Please try again later.
            </div>
            <?php
        }
    }
    //
    // Import Customers Modal, Import Customers
    //
    elseif (!empty($_POST['a']) && $_POST['a'] == 'importCustomers'
    ) {
        \Errors::debugLogger(__FILE__ . __LINE__);
        $approvedColumns = array('firstName',
            //'middleName',
            'lastName',
            //'phone',
            'email',
            'notes',
            'username',
            'passphrase',
            //'customCustomerID',
            'companyName'
        );
        if (is_file($_FILES['csvFile']['tmp_name'])) {
            if ($_FILES['csvFile']['type'] != 'text/plain') {
                echo 'Invalid file.';
                return false;
            }
            $row    = 1;
            if (($handle = fopen($_FILES['csvFile']['tmp_name'], "r")) !== FALSE) {
                $columns = array();
                $total   = array();
                while (($data    = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $num = count($data);
                    // Columns Declaration
                    if ($row == 1) {
                        for ($c = 0; $c < $num; $c++) {
                            $aci = array_search(strtolower(trim($data[$c])), array_map('strtolower', $approvedColumns));
                            if (empty($aci)) {
                                echo 'Invalid column: ' . $data[$c];
                                return false;
                            }
                            $columns[] = trim($approvedColumns[$aci]);
                            $colCount  = $num;
                        }
                        $row++;
                        continue;
                    }
                    // Number of columns check
                    if ($num != $colCount) {
                        echo 'Invalid column count on row #' . $row;
                        return false;
                    }
                    for ($c = 0; $c < $num; $c++) {
                        $total[$row][$columns[$c]] = trim($data[$c]);
                    }
                    $row++;
                }
                fclose($handle);
            }
        } else {
            echo 'Not a File! ' . $_POST['csvFile'];
            return false;
        }

        // Sanitize each entry and Create
        $s            = new killerCart\Sanitize();
        $customer     = new killerCart\Customer();
        $cartID      = $s->filterSingle($_POST['cartID'], FILTER_SANITIZE_NUMBER_INT);
        $groupID      = $s->filterSingle($_POST['groupID'], FILTER_SANITIZE_NUMBER_INT);
        $loginAllowed = 1;
        $row          = 1;
        $errors       = 0;
        $success      = 0;
        $msg          = '<ul>';
        foreach ($total as $k => $c) {
            $row++;
            $email      = $s->filterSingle($c['email'], FILTER_SANITIZE_EMAIL);
            $username   = $s->filterSingle($c['username'], FILTER_SANITIZE_SPECIAL_CHARS);
            $passphrase = $s->filterSingle($c['passphrase'], FILTER_SANITIZE_SPECIAL_CHARS);
            // Check for duplicate customer
            // @todo
            // Create New Customer
            $customerID = NULL;
            $customerID = $customer->createCustomer($cartID, $groupID, '', NULL, $email, $username, $passphrase, NULL, $loginAllowed);
            if (empty($customerID)) {
                $msg .= '<li>Failed to create account for row #' . $row . ' (' . $email . ')</li>';
                $errors++;
            } else {
                $msg .= '<li>Created account #' . $customerID . ' for ' . $email . '</li>';
                $success++;
            }

            // Check for username
            if (empty($username)) {
                $msg .= '<li>Notice:<ul><li>#' . $customerID . ' does not have a login username</li></ul></li>';
            }
        }
        $msg .= '</ul>';
        ?>
        <div class="alert alert-block alert-success span4">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <h4><i class="icon-ok"></i>&nbsp;Complete!</h4>
            Customers Imported: <strong><?php echo $success; ?></strong><br />
            Failed to Import: <strong><?php echo $errors; ?></strong>
            <?php echo $msg; ?>
        </div>
        <?php
    }

    exit;
    // End Ajax
}
?>

<div id="cart_admin_customer_container" class="container">
    <div class="page-header">
        <h1>Customer Management&nbsp;<small>Viewing all customers</small></h1>
    </div>

    <!-- Button: Add New Customer -->
    <a href="#newCustomerModal" role="button" class="btn" data-toggle="modal">
        <i class='icon-plus'></i>&nbsp;Add New Customer
    </a>

    <!-- Button: Import Customers -->
    <a href="#importCustomersModal" role="button" class="btn" data-toggle="modal">
        <i class='icon-list'></i>&nbsp;Import Customers
    </a>

    <?php
//
// New Customer Modal
//
    include(cartPrivateDir . 'templates/' . $_SESSION['cartTheme'] . '/admin/customer/customer_new_form.inc.phtml');

//
// Import Customers Modal
//
    include(cartPrivateDir . 'templates/' . $_SESSION['cartTheme'] . '/admin/customer/customer_import.inc.phtml');

//
// Customer List
//
    include(cartPrivateDir . 'templates/' . $_SESSION['cartTheme'] . '/admin/customer/customer_list.inc.phtml');

//
// Customer Edit Modal
//
    include(cartPrivateDir . 'templates/' . $_SESSION['cartTheme'] . '/admin/customer/customer_edit.inc.phtml');
    ?>
</div>