<?php

class Install extends Model
{
    /**
     * Takes data from wizard form and creates first brand as well
     * as some other initial database defaults
     * 
     * @param type $brandName
     * @param type $brandLabel
     * @param type $adminUsername
     * @param type $adminPassphrase
     * @param type $adminEmail
     * @param type $domainName
     */
    public function InstallDefaultData($brandName, $brandLabel, $brandMetaTitle, $adminUsername, $adminPassphrase, $adminEmail, $domainName)
    {
        // Parent Item Types
        $this->update(array('parentTypeLabel' => 'User'), "refParentItemTypes");
        $this->update(array('parentTypeLabel' => 'Page'), "refParentItemTypes");
        $this->update(array('parentTypeLabel' => 'Article'), "refParentItemTypes");
        $this->update(array('parentTypeLabel' => 'Comment'), "refParentItemTypes");

        // Remove existing rewrite map if exists
        $map = ROOT . DS . "url-alias-map.txt";
        if (is_file($map)) {
            rename($map, $map . '.' . time() . '.bak');
        }

        // Prepare Database for Cart
        self::PrepareDatabaseForCart();

        /* Rewrite Map defaults */
        $RewriteMapping = new RewriteMapping();
        // Global: /owners -> /admin/home
        $RewriteMapping->update(array(
            'aliasURL'  => '/owners',
            'actualURL' => '/admin/home', 'sortOrder' => 1, 'domainID'  => NULL));
        // Global: /locations -> /stores/readall
        $RewriteMapping->update(array(
            'aliasURL'  => '/locations',
            'actualURL' => '/stores/readall', 'sortOrder' => 2, 'domainID'  => NULL));
        // Global: /login -> /users/login
        $RewriteMapping->update(array(
            'aliasURL'  => '/login',
            'actualURL' => '/users/login', 'sortOrder' => 3, 'domainID'  => NULL));
        // Global: /logout -> /users/logout
        $RewriteMapping->update(array(
            'aliasURL'  => '/logout',
            'actualURL' => '/users/logout', 'sortOrder' => 4, 'domainID'  => NULL));
        // Global: /profile -> /users/update
        $RewriteMapping->update(array(
            'aliasURL'  => '/profile',
            'actualURL' => '/users/update', 'sortOrder' => 5, 'domainID'  => NULL));
        // Global: /stores -> /stores/readall
        $RewriteMapping->update(array(
            'aliasURL'  => '/stores',
            'actualURL' => '/stores/readall', 'sortOrder' => 6, 'domainID'  => NULL));

        // Brand
        $Brand                = new Brand();
        $brand['brandID']     = 1;
        $brand['brandName']   = $brandName;
        $brand['brandActive'] = 1;
        $brand['brandLabel']  = $brandLabel;
        $brand['brandMetaTitle'] = $brandMetaTitle;
        $brand['brandEmail']  = $adminEmail;
        $brand['activeTheme'] = "default";
        $Brand->update($brand);

        $brand['domains'] = array($domainName);
        // Creates brand defaults:
        // --Menus
        // --Usergroups (+ACL)
        // --User (Store Owner)
        // --Domains
        // --Cart
        BrandsController::createStepFinish($brand['brandID'], $brand);

        // Global Administrator
        $Usergroup           = new Usergroup();
        $usergroup           = $Usergroup->getSingle(array('brandID'       => $brand['brandID'], 'usergroupName' => 'Administrators'));
        $User                = new User();
        $user                = array();
        $user['userID']      = "DEFAULT";
        $user['usergroupID'] = $usergroup['usergroupID'];
        $user['userActive']  = 1;
        $user['userName']    = $adminUsername;
        $user['passphrase']  = $adminPassphrase;
        $user['userEmail']   = $adminEmail;
        $user['notes']       = "Global Administrator";
        $User->update($user);
    }

    public function PopulateSampleData()
    {
        /* Test */
        $brands    = array();
        $brands[]  = array('brandName'  => "Goin&#039; Postal",
            'brandLabel' => 'GP',
            'brandMetaTitle' => 'Goin&#039; Postal, Low cost shipping and packaging franchise',
            'brandEmail' => 'info@goinpostal.com',
            'domains' => array('dev.goinpostal.com', 'goinpostal.local'));
        $brands[]  = array('brandName'  => 'Hut no. 8',
            'brandLabel' => 'Hut8',
            'brandMetaTitle' => 'Hut no. 8 Resale Clothing Franchise Oportunities',
            'brandEmail' => 'info@hutno8.com',
            'domains' => array('dev.hutno8.com', 'hutno8.local'));
        $brands[]  = array('brandName'  => "Vino n Brew",
            'brandLabel' => 'VB',
            'brandMetaTitle' => 'Vino n Brew wine and beer',
            'brandEmail' => 'info@vinonbrew.com',
            'domains' => array('dev.vb.com', 'vb.local'));
        $Brand     = new Brand();
        $Store     = new Store();
        $Usergroup = new Usergroup();
        $User      = new User();

        $resultsMsg = "";
        foreach ($brands as $brand) {
            $resultsMsg .= "<br/>Adding <strong>" . $brand['brandName'] . "</strong>...";

            // Brand
            $domains = $brand['domains'];
            // remove domains array so we can save brand to db
            unset($brand['domains']);
            $brand['brandActive'] = 1;
            $brand['activeTheme'] = "default";
            $brand['brandID']     = $Brand->update($brand);
            $brand['domains'] = $domains;

            // Creates brand defaults:
            // --Menus
            // --Usergroups (+ACL)
            // --User (Store Owner)
            // --Domains
            // --Cart
            BrandsController::createStepFinish($brand['brandID'], $brand);

            // Store
            $usergroup   = $Usergroup->getSingle(array('brandID'       => $brand['brandID'],
                'usergroupName' => "Store Owners"));
            $user        = $User->getSingle(array('usergroupID' => $usergroup['usergroupID']));
            $storeNumber = $brand['brandLabel'] . "01";
            $store       = array('brandID'     => $brand['brandID'],
                'ownerID'     => $user['userID'],
                'storeNumber' => $storeNumber,
                'storeName'   => $storeNumber,
                'storeActive' => 1,
                'storeTheme'  => 'default');
            $storeID     = $Store->update($store);

            // Create default cart for store
            Install::InstallCart($storeID, $brand['brandID']);

            $resultsMsg .= "<strong style='color:green;'>Success!</strong>";
        }
        return $resultsMsg;
    }

    /**
     * Prepares database for cart use
     * 
     * @return boolean
     */
    private function PrepareDatabaseForCart()
    {
        Errors::debugLogger(__METHOD__, 1, true);
        
        // First use only
        $DB    = new \Database();
        $query = "

            ALTER TABLE brands AUTO_INCREMENT = 1;
            ALTER TABLE domains AUTO_INCREMENT = 100;
            ALTER TABLE carts AUTO_INCREMENT = 10000;
            ALTER TABLE users AUTO_INCREMENT = 10000;
            ALTER TABLE usergroups AUTO_INCREMENT = 10000;
            ALTER TABLE cartsPaymentGateways AUTO_INCREMENT = 1;
            ALTER TABLE customers AUTO_INCREMENT = 10000;
            ALTER TABLE customerOrders AUTO_INCREMENT = 10000;
            ALTER TABLE productCategories AUTO_INCREMENT = 10000;
            ALTER TABLE products AUTO_INCREMENT = 10000;
            ALTER TABLE productCategoriesSettings AUTO_INCREMENT = 1000;
            ALTER TABLE productOptions AUTO_INCREMENT = 1000;
            ALTER TABLE productOptionsChoices AUTO_INCREMENT = 100;
            ALTER TABLE productOptionsCustom AUTO_INCREMENT = 100;
            ALTER TABLE productOptionsChoicesCustom AUTO_INCREMENT = 100;
            

            INSERT INTO `refPaymentMethodTypes`
            (paymentMethodCode, paymentMethodDescription)
            VALUES
            ('BTC', 'Bitcoin'),
            ('AMEX', 'American Express'),
            ('MC', 'Master Card'),
            ('VISA', 'VISA'),
            ('DISC', 'Discover'),
            ('CHK', 'Check'),
            ('ECHK', 'eCheck'),
            ('WIRE', 'Wire Transfer'),
            ('PAYP', 'PayPal'),
            ('CASH', 'Cash');


            INSERT INTO `refAddressTypes`
            (addressTypeCode, addressTypeDescription, sortOrder)
            VALUES
            ('RES', 'Residential', 1),
            ('BUS', 'Business', 2),
            ('APT', 'Apartment', 3);


            INSERT INTO `refOrderStatusCodes`
            (orderStatusCode, orderStatusDescription, sortOrder)
            VALUES
            ('INC', 'Incomplete', 0),
            ('PND', 'Pending', 1),
            ('ATH', 'Authorized', 2),
            ('PD', 'Paid', 3),
            ('DCL', 'Declined', 4),
            ('CNC', 'Cancelled', 5),
            ('RFN', 'Refunded', 6),
            ('VD', 'Voided', 7),
            ('CMP', 'Completed', 8),
            ('ARC', 'Archived', 9);


            INSERT INTO `refDeliveryStatusCodes`
            (deliveryStatusCode, deliveryStatusDescription, sortOrder)
            VALUES
            ('PND', 'Pending', 1),
            ('RDY', 'Ready to Ship', 2),
            ('SHP', 'Shipped', 3),
            ('SPP', 'Partially Shipped', 4),
            ('RTN', 'Returned', 5),
            ('RTP', 'Partially Returned', 6),
            ('CNC', 'Cancelled', 7),
            ('CMP', 'Completed', 8),
            ('ARC', 'Archived', 9);


            INSERT INTO `paymentGateways`
            (gatewayID, gatewayName, gatewayURL, gatewayUsername, gatewayPassphrase, gatewayNotes, gatewayTemplate, gatewayOffline)
            VALUES
            (1, 'Offline Processing', '', '', '', 'Offline Processing', 'offlineCheckout', 1),
            (2, 'MercuryPay Test Account', 'https://w1.mercurydev.net/ws/ws.asmx?WSDL', '023358150511666', 'xyz', 'MercuryPay Ecommerce Account', 'mercuryPayCheckout', null);
            ";
        $res = $DB->query($query);
        return true;
    }

    /**
     * Creates cart for store
     * 
     * @param int $storeID
     * @return int cartID
     */
    public static function InstallCart($storeID = NULL, $brandID = NULL)
    {
        $Store = new Store();
        if (!empty($storeID)) {
            $store     = $Store->getSingle(array('storeID' => $storeID));
            $cartName  = $store['storeNumber'];
            $addressID = $store['addressID'];
            $email     = $store['email'];
            $brandID   = $store['brandID'];
        } elseif (!empty($brandID)) {
            $Brand     = new Brand();
            $brand     = $Brand->getSingle(array('brandID' => $brandID));
            $cartName  = $brand['brandName'];
            $addressID = $brand['addressID'];
            $email     = $brand['brandEmail'];
        }

        if (empty($addressID)) {
            $addressID = "NULL";
        }

        $tos = <<<___EOS
<h3>
    1. Terms
</h3>

<p>
    By accessing this web site, you are agreeing to be bound by these 
    web site Terms and Conditions of Use, all applicable laws and regulations, 
    and agree that you are responsible for compliance with any applicable local 
    laws. If you do not agree with any of these terms, you are prohibited from 
    using or accessing this site. The materials contained in this web site are 
    protected by applicable copyright and trade mark law.
</p>

<ol type="a">
    <li>
        Shipping for these products will be charged at published rate for the carrier of our choice (the cheapest and best way possible), 
        and will be billed to your credit card separately from the order as soon as we know how much it was.  THIS POLICY IS NON-NEGOTIABLE.
</ol>

<h3>
    2. Use License
</h3>

<ol type="a">
    <li>
        Permission is granted to temporarily download one copy of the materials 
        (information or software) on $cartName's web site for personal, 
        non-commercial transitory viewing only. This is the grant of a license, 
        not a transfer of title, and under this license you may not:

        <ol type="i">
            <li>modify or copy the materials;</li>
            <li>use the materials for any commercial purpose, or for any public display (commercial or non-commercial);</li>
            <li>attempt to decompile or reverse engineer any software contained on $cartName's web site;</li>
            <li>remove any copyright or other proprietary notations from the materials; or</li>
            <li>transfer the materials to another person or "mirror" the materials on any other server.</li>
        </ol>
    </li>
    <li>
        This license shall automatically terminate if you violate any of these restrictions and may be terminated by $cartName at any time. Upon terminating your viewing of these materials or upon the termination of this license, you must destroy any downloaded materials in your possession whether in electronic or printed format.
    </li>
</ol>

<h3>
    3. Disclaimer
</h3>

<ol type="a">
    <li>
        The materials on $cartName's web site are provided "as is". $cartName makes no warranties, expressed or implied, and hereby disclaims and negates all other warranties, including without limitation, implied warranties or conditions of merchantability, fitness for a particular purpose, or non-infringement of intellectual property or other violation of rights. Further, $cartName does not warrant or make any representations concerning the accuracy, likely results, or reliability of the use of the materials on its Internet web site or otherwise relating to such materials or on any sites linked to this site.
    </li>
</ol>

<h3>
    4. Limitations
</h3>

<p>
    In no event shall $cartName or its suppliers be liable for any damages (including, without limitation, damages for loss of data or profit, or due to business interruption,) arising out of the use or inability to use the materials on $cartName's Internet site, even if $cartName or a $cartName authorized representative has been notified orally or in writing of the possibility of such damage. Because some jurisdictions do not allow limitations on implied warranties, or limitations of liability for consequential or incidental damages, these limitations may not apply to you.
</p>

<h3>
    5. Revisions and Errata
</h3>

<p>
    The materials appearing on $cartName's web site could include technical, typographical, or photographic errors. $cartName does not warrant that any of the materials on its web site are accurate, complete, or current. $cartName may make changes to the materials contained on its web site at any time without notice. $cartName does not, however, make any commitment to update the materials.
</p>

<h3>
    6. Links
</h3>

<p>
    $cartName has not reviewed all of the sites linked to its Internet web site and is not responsible for the contents of any such linked site. The inclusion of any link does not imply endorsement by $cartName of the site. Use of any such linked web site is at the user's own risk.
</p>

<h3>
    7. Site Terms of Use Modifications
</h3>

<p>
    $cartName may revise these terms of use for its web site at any time without notice. By using this web site you are agreeing to be bound by the then current version of these Terms and Conditions of Use.
</p>

<h3>
    8. Governing Law
</h3>

<p>
    Any claim relating to $cartName's web site shall be governed by the laws of the State of Florida without regard to its conflict of law provisions.
</p>

<p>
    General Terms and Conditions applicable to Use of a Web Site.
</p>



<h2>
    Privacy Policy
</h2>

<p>
    Your privacy is very important to us. Accordingly, we have developed this Policy in order for you to understand how we collect, use, communicate and disclose and make use of personal information. The following outlines our privacy policy.
</p>

<ul>
    <li>
        Before or at the time of collecting personal information, we will identify the purposes for which information is being collected.
    </li>
    <li>
        We will collect and use of personal information solely with the objective of fulfilling those purposes specified by us and for other compatible purposes, unless we obtain the consent of the individual concerned or as required by law.		
    </li>
    <li>
        We will only retain personal information as long as necessary for the fulfillment of those purposes. 
    </li>
    <li>
        We will collect personal information by lawful and fair means and, where appropriate, with the knowledge or consent of the individual concerned. 
    </li>
    <li>
        Personal data should be relevant to the purposes for which it is to be used, and, to the extent necessary for those purposes, should be accurate, complete, and up-to-date. 
    </li>
    <li>
        We will protect personal information by reasonable security safeguards against loss or theft, as well as unauthorized access, disclosure, copying, use or modification.
    </li>
    <li>
        We will make readily available to customers information about our policies and practices relating to the management of personal information. 
    </li>
</ul>

<p>
    We are committed to conducting our business in accordance with these principles in order to ensure that the confidentiality of personal information is protected and maintained. 
</p>
___EOS;

        // Cart        
        $data   = array('cartName'       => $cartName,
            'cartActive'     => 1,
            'cartNotes'      => '',
            'cartTheme'      => 'default',
            'addressID'      => $addressID,
            'emailOrders'    => $email,
            'emailContact'   => $email,
            'emailErrors'    => $email,
            'termsOfService' => $tos);
        $table  = "carts";
        $cartID = $Store->update($data, $table);

        // Update parent with new cart ID
        if (!empty($storeID)) {
            $data = array('storeID' => $storeID,
                'brandID' => $brandID,
                'cartID'  => $cartID);
            $Store->update($data);
        } elseif (!empty($brandID)) {
            $Brand = new Brand();
            $data  = array('brandID'     => $brandID,
                'brandName'   => $brand['brandName'],
                'brandActive' => $brand['brandActive'],
                'brandLabel'  => $brand['brandLabel'],
                'cartID'      => $cartID);
            $Brand->update($data);
        }

        // Associate Payment Gateways to Store
        $data  = array('cartID'        => $cartID,
            'gatewayID'     => 1, // Offline
            'gatewayActive' => 1);
        $table = "cartsPaymentGateways";
        $Store->update($data, $table);

        /*
         * -- Admin User
         * -- Username: cartadmin
         * -- Password: test1234
         */
//        $data = array('userID' => 'DEFAULT',
//            'cartID' => $cartID,
//            'username' => "cartadmin",
//            'password' => '$6$rounds=5046$5155df157f44a8.3$eZMPTyPOJr14r8x8pLrotHVv3PWcizwGltS7SdXnCJfXN.6BR3/KZnwiwDo1kpdOyYiQDAlw..SbhNg8AQe1V.',
//            'email' => $email,
//            'userActive' => 1,
//            'userNotes' => 'Cart Administrator Account',
//            );
//        $table = "cartUsers";
//        $userID = $Store->update($data, $table);
        // Add user to Cart Administrator group (#2)
//        if ($cartID == 1)
//        {
//            $groupID = 1; // GLOBAL cart Admin
//        }
//        else {
//            $groupID = 2; // STORE cart Admin
//        }
//        $data = array('cartID' => $cartID,
//            'groupID' => $groupID,
//            'userID' => $userID);
//        $table = "cartGroupUsers";
//        $Store->update($data, $table);

        return $cartID;
    }

}