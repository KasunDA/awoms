USE AWOMS;

-- Auto Increment Numbers --
ALTER TABLE carts AUTO_INCREMENT = 1;
ALTER TABLE storeUsers AUTO_INCREMENT = 1;
ALTER TABLE storesPaymentGateways AUTO_INCREMENT = 1;
ALTER TABLE customers AUTO_INCREMENT = 10000;
ALTER TABLE customerOrders AUTO_INCREMENT = 10000;
ALTER TABLE productCategories AUTO_INCREMENT = 1;
ALTER TABLE products AUTO_INCREMENT = 10000;
ALTER TABLE productCategoriesSettings AUTO_INCREMENT = 1;
ALTER TABLE productOptions AUTO_INCREMENT = 1;
ALTER TABLE productOptionsChoices AUTO_INCREMENT = 1;
ALTER TABLE productOptionsCustom AUTO_INCREMENT = 1;
ALTER TABLE productOptionsChoicesCustom AUTO_INCREMENT = 1;
ALTER TABLE images AUTO_INCREMENT = 1;
ALTER TABLE addresses AUTO_INCREMENT = 1;

-- Payment Methods
INSERT INTO `refPaymentMethodTypes`
(paymentMethodCode, paymentMethodDescription)
VALUES
('AMEX', 'American Express'),
('MC', 'Master Card'),
('VISA', 'VISA'),
('DISC', 'Discover'),
('CHK', 'Check'),
('ECHK', 'eCheck'),
('WIRE', 'Wire Transfer'),
('PAYP', 'PayPal'),
('CASH', 'Cash');

-- Address Types
INSERT INTO `refAddressTypes`
(addressTypeCode, addressTypeDescription, sortOrder)
VALUES
('RES', 'Residential', 1),
('BUS', 'Business', 2),
('APT', 'Apartment', 3);

-- Order Status Codes
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

-- Delivery Status Codes
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

-- Store Payment Gateway
INSERT INTO `paymentGateways`
(gatewayID, gatewayName, gatewayURL, gatewayUsername, gatewayPassphrase, gatewayNotes, gatewayTemplate, gatewayOffline)
VALUES
(1, 'Offline Processing', '', '', '', 'Offline Processing', 'offlineCheckout', 1),
(2, 'MercuryPay Test Account', 'https://w1.mercurydev.net/ws/ws.asmx?WSDL', '023358150511666', 'xyz', 'MercuryPay Ecommerce Account', 'mercuryPayCheckout', null);

-- Store Group
INSERT INTO `storeUserGroups`
(groupID, groupName, groupDescription)
VALUES
(1, 'Global Administrator', 'Unrestricted access to all stores'),
(2, 'Store Administrator', 'Unrestricted access to this store'),
(3, 'Accounting', 'Access to order billing'),
(4, 'Shipping', 'Access to order shipping');

-- Store Address
INSERT INTO `addresses`
(addressID, line1, line2, line3, city, zipPostcode, stateProvinceCounty, country, addressNotes)
VALUES
(111, '4941 4th Street', '', '', 'Zephyrhills', '33542', 'FL', 'USA', 'Franchise Headquarters');

-- Carts
INSERT INTO `carts`
(cartID, cartName, cartActive, cartNotes, cartTheme, addressID, emailOrders, emailContact, emailErrors)
VALUES
(1, 'Goin&#39; Postal Franchise Corporation', 1, '', 'default', 111, 'brock@goinpostal.com', 'brock@goinpostal.com', 'brock@goinpostal.com');

-- Associate Payment Gateways to Store
INSERT INTO `storesPaymentGateways`
(cartID, gatewayID, gatewayActive)
VALUES
(1, 1, 0),
(1, 2, 1);

-- Global Admin User
-- Username: cartadmin
-- Password: test1234
INSERT INTO `storeUsers`
(userID, username, password, email, userActive, userNotes)
VALUES
(
1,
'cartadmin',
'$6$rounds=5046$5155df157f44a8.3$eZMPTyPOJr14r8x8pLrotHVv3PWcizwGltS7SdXnCJfXN.6BR3/KZnwiwDo1kpdOyYiQDAlw..SbhNg8AQe1V.',
'brock@goinpostal.com',
1,
'Global Administrator Account'
);

-- Group Users
INSERT INTO `storeGroupUsers`
(cartID, groupID, userID)
VALUES
(1, 1, 1);
