-- Model: Cart    Version: 00.00.00
SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Schema GPFCCMS
-- -----------------------------------------------------
DROP SCHEMA IF EXISTS `GPFCCMS` ;
CREATE SCHEMA IF NOT EXISTS `GPFCCMS` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ;
USE `GPFCCMS` ;

-- -----------------------------------------------------
-- Table `lostPasswords` -- ! MySQL Workbench not working so created this outside of mwb file !
-- -----------------------------------------------------
DROP TABLE IF EXISTS `lostPasswords` ;

CREATE TABLE IF NOT EXISTS `lostPasswords` (
  `ID` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tempCode` VARCHAR(255) NOT NULL,
  `userID` BIGINT(20) UNSIGNED NOT NULL,
  `dateCreated` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE INDEX `lostpassword_id_UNIQUE` (`ID` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `addresses`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `addresses` ;

CREATE TABLE IF NOT EXISTS `addresses` (
  `addressID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `firstName` VARCHAR(255) NULL,
  `middleName` VARCHAR(255) NULL,
  `lastName` VARCHAR(255) NULL,
  `phone` VARCHAR(45) NULL,
  `email` VARCHAR(255) NULL,
  `line1` VARCHAR(255) NOT NULL,
  `line2` VARCHAR(255) NULL,
  `line3` VARCHAR(255) NULL,
  `city` VARCHAR(255) NULL,
  `zipPostcode` VARCHAR(255) NULL,
  `stateProvinceCounty` VARCHAR(255) NULL,
  `country` VARCHAR(255) NULL,
  `addressNotes` TEXT NULL,
  PRIMARY KEY (`addressID`),
  UNIQUE INDEX `address_id_UNIQUE` (`addressID` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `carts`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `carts` ;

CREATE TABLE IF NOT EXISTS `carts` (
  `cartID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `cartName` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL,
  `cartActive` TINYINT(1) UNSIGNED ZEROFILL NULL,
  `cartNotes` TEXT CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL,
  `cartTheme` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL,
  `addressID` BIGINT UNSIGNED NULL,
  `emailOrders` VARCHAR(255) NULL,
  `emailContact` VARCHAR(255) NULL,
  `emailErrors` VARCHAR(255) NULL,
  `cartPublicKey` BLOB NULL,
  `termsOfService` TEXT NULL,
  `storefrontCarousel` TINYINT(1) NULL,
  `storefrontCategories` TINYINT(1) NULL,
  `storefrontDescription` TEXT NULL,
  PRIMARY KEY (`cartID`),
  UNIQUE INDEX `store_id_UNIQUE` (`cartID` ASC),
  INDEX `fk_stores_addresses1_idx` (`addressID` ASC),
  CONSTRAINT `fk_stores_addresses1`
    FOREIGN KEY (`addressID`)
    REFERENCES `addresses` (`addressID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 3;


-- -----------------------------------------------------
-- Table `customers`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `customers` ;

CREATE TABLE IF NOT EXISTS `customers` (
  `customerID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `cartID` BIGINT UNSIGNED NOT NULL,
  `customCustomerID` VARCHAR(255) NULL,
  PRIMARY KEY (`customerID`, `cartID`),
  INDEX `fk_customers_stores1_idx` (`cartID` ASC),
  UNIQUE INDEX `customer_id_UNIQUE` (`customerID` ASC),
  CONSTRAINT `fk_customers_stores1`
    FOREIGN KEY (`cartID`)
    REFERENCES `carts` (`cartID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `refOrderStatusCodes`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `refOrderStatusCodes` ;

CREATE TABLE IF NOT EXISTS `refOrderStatusCodes` (
  `orderStatusCode` VARCHAR(45) NOT NULL,
  `orderStatusDescription` TEXT NULL,
  `sortOrder` TINYINT(1) NULL,
  PRIMARY KEY (`orderStatusCode`),
  UNIQUE INDEX `order_status_code_UNIQUE` (`orderStatusCode` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `refDeliveryStatusCodes`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `refDeliveryStatusCodes` ;

CREATE TABLE IF NOT EXISTS `refDeliveryStatusCodes` (
  `deliveryStatusCode` VARCHAR(45) NOT NULL,
  `deliveryStatusDescription` TEXT NULL,
  `sortOrder` TINYINT(1) NULL,
  PRIMARY KEY (`deliveryStatusCode`),
  UNIQUE INDEX `delivery_status_code_UNIQUE` (`deliveryStatusCode` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `paymentMethods`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `paymentMethods` ;

CREATE TABLE IF NOT EXISTS `paymentMethods` (
  `paymentMethodID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `number1` TEXT NULL,
  `number2` TEXT NULL,
  `number3` TEXT NULL,
  `expMonth` TEXT NULL,
  `expYear` TEXT NULL,
  `paymentMethodNotes` TEXT NULL,
  PRIMARY KEY (`paymentMethodID`),
  UNIQUE INDEX `payment_method_id_UNIQUE` (`paymentMethodID` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `customerOrders`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `customerOrders` ;

CREATE TABLE IF NOT EXISTS `customerOrders` (
  `orderID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `customerID` BIGINT UNSIGNED NOT NULL,
  `orderStatusCode` VARCHAR(45) NOT NULL,
  `deliveryStatusCode` VARCHAR(45) NOT NULL,
  `shippingAddressID` BIGINT UNSIGNED NULL,
  `paymentMethodID` BIGINT UNSIGNED NULL,
  `dateOrderPlaced` DATETIME NULL,
  `dateOrderPaid` DATETIME NULL,
  `orderNotes` TEXT NULL,
  `orderIP` VARCHAR(15) NULL,
  `totalOrderPrice` DECIMAL(13,2) NULL,
  `totalOrderTax` DECIMAL(13,2) NULL,
  `totalOrderDelivery` DECIMAL(13,2) NULL,
  `orderTaxableAmount` DECIMAL(13,2) NULL,
  PRIMARY KEY (`orderID`, `customerID`),
  UNIQUE INDEX `orderID_UNIQUE` (`orderID` ASC),
  INDEX `fk_customerOrders_refOrderStatusCodes1_idx` (`orderStatusCode` ASC),
  INDEX `fk_customerOrders_refDeliveryStatusCodes1_idx` (`deliveryStatusCode` ASC),
  INDEX `fk_customerOrders_addresses1_idx` (`shippingAddressID` ASC),
  INDEX `fk_customerOrders_paymentMethods1_idx` (`paymentMethodID` ASC),
  INDEX `fk_customerOrders_customers1_idx` (`customerID` ASC),
  CONSTRAINT `fk_customerOrders_refOrderStatusCodes1`
    FOREIGN KEY (`orderStatusCode`)
    REFERENCES `refOrderStatusCodes` (`orderStatusCode`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_customerOrders_refDeliveryStatusCodes1`
    FOREIGN KEY (`deliveryStatusCode`)
    REFERENCES `refDeliveryStatusCodes` (`deliveryStatusCode`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_customerOrders_addresses1`
    FOREIGN KEY (`shippingAddressID`)
    REFERENCES `addresses` (`addressID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_customerOrders_paymentMethods1`
    FOREIGN KEY (`paymentMethodID`)
    REFERENCES `paymentMethods` (`paymentMethodID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_customerOrders_customers1`
    FOREIGN KEY (`customerID`)
    REFERENCES `customers` (`customerID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `productCategories`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `productCategories` ;

CREATE TABLE IF NOT EXISTS `productCategories` (
  `categoryID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `categoryCode` VARCHAR(45) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL,
  `categoryActive` TINYINT(1) NOT NULL DEFAULT 1,
  `categoryName` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL,
  `categoryDescriptionPublic` TEXT CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL,
  `categoryDescriptionPrivate` TEXT NULL,
  `categoryTaxable` TINYINT(1) NULL,
  `categoryPrivate` TINYINT(1) NULL,
  `categoryShowPrice` TINYINT(1) NULL DEFAULT 1,
  PRIMARY KEY (`categoryID`),
  UNIQUE INDEX `category_id_UNIQUE` (`categoryID` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 3;


-- -----------------------------------------------------
-- Table `products`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `products` ;

CREATE TABLE IF NOT EXISTS `products` (
  `productID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `categoryID` BIGINT UNSIGNED NOT NULL,
  `parentProductID` BIGINT UNSIGNED NULL,
  `productActive` TINYINT(1) NOT NULL DEFAULT 1,
  `productName` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `price` DECIMAL(13,2) NOT NULL DEFAULT 0,
  `productDescriptionPublic` TEXT CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL,
  `productDescriptionPrivate` TEXT NULL,
  `productTaxable` TINYINT(1) NULL,
  `productPrivate` TINYINT(1) NULL,
  `productEmail` VARCHAR(255) NULL,
  `productSKU` VARCHAR(255) NULL,
  `productSpecifications` TEXT NULL,
  `productShipping` TEXT NULL,
  `productWarranty` TEXT NULL,
  PRIMARY KEY (`productID`, `categoryID`),
  UNIQUE INDEX `product_id_UNIQUE` (`productID` ASC),
  INDEX `fk_products_products1_idx` (`parentProductID` ASC),
  INDEX `fk_products_product_categories1_idx` (`categoryID` ASC),
  CONSTRAINT `fk_products_products1`
    FOREIGN KEY (`parentProductID`)
    REFERENCES `products` (`productID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_products_product_categories1`
    FOREIGN KEY (`categoryID`)
    REFERENCES `productCategories` (`categoryID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `customerOrderProducts`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `customerOrderProducts` ;

CREATE TABLE IF NOT EXISTS `customerOrderProducts` (
  `orderID` BIGINT UNSIGNED NOT NULL,
  `productID` BIGINT UNSIGNED NOT NULL,
  `quantity` INT NULL,
  `comments` VARCHAR(1000) NULL,
  `deliveryPrice` DECIMAL(13,2) NULL,
  PRIMARY KEY (`orderID`, `productID`),
  INDEX `fk_customer_order_products_products1_idx` (`productID` ASC),
  CONSTRAINT `fk_customer_order_products_customer_orders1`
    FOREIGN KEY (`orderID`)
    REFERENCES `customerOrders` (`orderID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_customer_order_products_products1`
    FOREIGN KEY (`productID`)
    REFERENCES `products` (`productID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Product information for order.';


-- -----------------------------------------------------
-- Table `productCategoriesSettings`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `productCategoriesSettings` ;

CREATE TABLE IF NOT EXISTS `productCategoriesSettings` (
  `settingID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `categoryID` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(45) NOT NULL,
  `type` VARCHAR(45) NOT NULL,
  `value` VARCHAR(255) NULL DEFAULT NULL,
  PRIMARY KEY (`settingID`, `categoryID`, `name`),
  UNIQUE INDEX `setting_id_UNIQUE` (`settingID` ASC),
  INDEX `fk_product_categories_settings_product_categories1_idx` (`categoryID` ASC),
  CONSTRAINT `fk_product_categories_settings_product_categories1`
    FOREIGN KEY (`categoryID`)
    REFERENCES `productCategories` (`categoryID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 3;


-- -----------------------------------------------------
-- Table `productOptions`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `productOptions` ;

CREATE TABLE IF NOT EXISTS `productOptions` (
  `optionID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `cartID` BIGINT UNSIGNED NOT NULL,
  `optionName` VARCHAR(255) NULL,
  `optionType` VARCHAR(255) NOT NULL,
  `optionActive` TINYINT(1) NOT NULL,
  `optionBehavior` VARCHAR(45) NULL,
  `optionRequired` TINYINT(1) NULL,
  PRIMARY KEY (`optionID`, `cartID`, `optionName`),
  UNIQUE INDEX `setting_id_UNIQUE` (`optionID` ASC),
  INDEX `fk_productOptions_stores1_idx` (`cartID` ASC),
  CONSTRAINT `fk_productOptions_stores1`
    FOREIGN KEY (`cartID`)
    REFERENCES `carts` (`cartID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `customerInfo`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `customerInfo` ;

CREATE TABLE IF NOT EXISTS `customerInfo` (
  `customerID` BIGINT UNSIGNED NOT NULL,
  `companyName` VARCHAR(255) NULL,
  `firstName` VARCHAR(255) NOT NULL,
  `middleName` VARCHAR(255) NULL,
  `lastName` VARCHAR(255) NULL,
  `phone` VARCHAR(255) NULL,
  `email` VARCHAR(255) NULL,
  `notes` TEXT NULL,
  `fingerprint` CHAR(128) NULL,
  `visitorIP` VARCHAR(15) NULL,
  `username` VARCHAR(255) NULL,
  `passphrase` CHAR(128) NULL,
  `regDate` DATETIME NULL,
  `lastLoginDate` DATETIME NULL,
  `lastLoginIP` CHAR(15) NULL,
  `lastFailedLoginDate` DATETIME NULL,
  `lastFailedLoginIP` CHAR(15) NULL,
  `failedLoginAttempts` TINYINT(1) NULL,
  `protectedPrivateKey` BLOB NULL,
  `loginAllowed` TINYINT(1) NULL,
  PRIMARY KEY (`customerID`),
  INDEX `fk_customer_info_customers1_idx` (`customerID` ASC),
  CONSTRAINT `fk_customer_info_customers1`
    FOREIGN KEY (`customerID`)
    REFERENCES `customers` (`customerID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `brands`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `brands` ;

CREATE TABLE IF NOT EXISTS `brands` (
  `brandID` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `brandName` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `brandActive` TINYINT(1) UNSIGNED NOT NULL,
  `brandLabel` VARCHAR(45) NOT NULL,
  `brandEmail` VARCHAR(255) NULL,
  `brandMetaTitle` VARCHAR(255) NULL,
  `brandMetaDescription` text,
  `brandMetaKeywords` varchar(255) DEFAULT NULL,
  `activeTheme` VARCHAR(45) NOT NULL DEFAULT 'default',
  `addressID` BIGINT UNSIGNED NULL,
  `cartID` BIGINT UNSIGNED NULL,
  `brandFavIcon` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`brandID`, `brandName`, `brandActive`),
  UNIQUE INDEX `brandID_UNIQUE` (`brandID` ASC),
  INDEX `fk_brands_addresses1_idx` (`addressID` ASC),
  INDEX `fk_brands_carts1_idx` (`cartID` ASC),
  CONSTRAINT `fk_brands_addresses1`
    FOREIGN KEY (`addressID`)
    REFERENCES `addresses` (`addressID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_brands_carts1`
    FOREIGN KEY (`cartID`)
    REFERENCES `carts` (`cartID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `sessions`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `sessions` ;

CREATE TABLE IF NOT EXISTS `sessions` (
  `fingerprint` CHAR(128) NOT NULL,
  `brandID` BIGINT(20) UNSIGNED NULL,
  `cartID` BIGINT UNSIGNED NULL,
  `setTime` CHAR(10) NOT NULL,
  `expiresTime` CHAR(10) CHARACTER SET 'utf8' COLLATE 'utf8_persian_ci' NOT NULL,
  `visitorIP` CHAR(46) NULL,
  `session` TEXT NULL,
  PRIMARY KEY (`fingerprint`),
  INDEX `fk_sessions_stores1_idx` (`cartID` ASC),
  UNIQUE INDEX `fingerprint_UNIQUE` (`fingerprint` ASC),
  INDEX `fk_sessions_brands1_idx` (`brandID` ASC),
  CONSTRAINT `fk_sessions_stores1`
    FOREIGN KEY (`cartID`)
    REFERENCES `carts` (`cartID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_sessions_brands1`
    FOREIGN KEY (`brandID`)
    REFERENCES `brands` (`brandID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `sessionSettings`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `sessionSettings` ;

CREATE TABLE IF NOT EXISTS `sessionSettings` (
  `fingerprint` CHAR(128) NOT NULL,
  `cartID` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(45) NOT NULL,
  `type` VARCHAR(45) NOT NULL,
  `value` TEXT NULL,
  PRIMARY KEY (`fingerprint`, `cartID`, `name`),
  INDEX `fk_sessions_settings_sessions1_idx` (`cartID` ASC, `fingerprint` ASC),
  CONSTRAINT `fk_sessions_settings_sessions1`
    FOREIGN KEY (`cartID` , `fingerprint`)
    REFERENCES `sessions` (`cartID` , `fingerprint`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `refCartsProductCategories`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `refCartsProductCategories` ;

CREATE TABLE IF NOT EXISTS `refCartsProductCategories` (
  `cartID` BIGINT UNSIGNED NOT NULL,
  `categoryID` BIGINT UNSIGNED NOT NULL,
  `parentCategoryID` BIGINT UNSIGNED NULL,
  `levelNumber` INT UNSIGNED NULL,
  PRIMARY KEY (`categoryID`),
  INDEX `fk_ref_stores_product_categories_product_categories1_idx` (`categoryID` ASC),
  INDEX `fk_ref_stores_product_categories_ref_stores_product_categor_idx` (`parentCategoryID` ASC),
  CONSTRAINT `fk_ref_stores_product_categories_stores1`
    FOREIGN KEY (`cartID`)
    REFERENCES `carts` (`cartID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_ref_stores_product_categories_product_categories1`
    FOREIGN KEY (`categoryID`)
    REFERENCES `productCategories` (`categoryID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_ref_stores_product_categories_ref_stores_product_categories1`
    FOREIGN KEY (`parentCategoryID`)
    REFERENCES `refCartsProductCategories` (`categoryID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `productPriceHistory`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `productPriceHistory` ;

CREATE TABLE IF NOT EXISTS `productPriceHistory` (
  `productID` BIGINT UNSIGNED NOT NULL,
  `dateFrom` DATETIME NOT NULL,
  `productPrice` DECIMAL(13,2) NOT NULL,
  PRIMARY KEY (`productID`),
  CONSTRAINT `fk_product_prices_products1`
    FOREIGN KEY (`productID`)
    REFERENCES `products` (`productID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `refAddressTypes`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `refAddressTypes` ;

CREATE TABLE IF NOT EXISTS `refAddressTypes` (
  `addressTypeCode` VARCHAR(45) NOT NULL,
  `addressTypeDescription` TEXT NULL,
  `sortOrder` TINYINT(1) NULL,
  PRIMARY KEY (`addressTypeCode`),
  UNIQUE INDEX `address_type_code_UNIQUE` (`addressTypeCode` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `customerAddresses`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `customerAddresses` ;

CREATE TABLE IF NOT EXISTS `customerAddresses` (
  `customerID` BIGINT UNSIGNED NOT NULL,
  `addressID` BIGINT UNSIGNED NOT NULL,
  `addressTypeCode` VARCHAR(45) NOT NULL,
  `dateFrom` DATETIME NOT NULL,
  `dateTo` DATETIME NULL,
  PRIMARY KEY (`customerID`, `addressID`, `addressTypeCode`, `dateFrom`),
  INDEX `fk_customer_addresses_addresses1_idx` (`addressID` ASC),
  INDEX `fk_customer_addresses_ref_address_types1_idx` (`addressTypeCode` ASC),
  CONSTRAINT `fk_customer_addresses_customers1`
    FOREIGN KEY (`customerID`)
    REFERENCES `customers` (`customerID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_customer_addresses_addresses1`
    FOREIGN KEY (`addressID`)
    REFERENCES `addresses` (`addressID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_customer_addresses_ref_address_types1`
    FOREIGN KEY (`addressTypeCode`)
    REFERENCES `refAddressTypes` (`addressTypeCode`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `refPaymentMethodTypes`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `refPaymentMethodTypes` ;

CREATE TABLE IF NOT EXISTS `refPaymentMethodTypes` (
  `paymentMethodCode` VARCHAR(45) NOT NULL,
  `paymentMethodDescription` TEXT NULL,
  PRIMARY KEY (`paymentMethodCode`),
  UNIQUE INDEX `payment_method_code_UNIQUE` (`paymentMethodCode` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `stores`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `stores` ;

CREATE TABLE IF NOT EXISTS `stores` (
  `storeID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `brandID` BIGINT(20) UNSIGNED NOT NULL,
  `cartID` BIGINT UNSIGNED NULL,
  `ownerID` BIGINT(20) UNSIGNED NULL,
  `storeNumber` VARCHAR(45) NULL,
  `storeActive` TINYINT(1) NULL,
  `storeTheme` VARCHAR(255) NULL,
  `storeName` VARCHAR(255) NULL,
  `addressID` BIGINT UNSIGNED NULL,
  `coding` VARCHAR(45) NULL,
  `companyName` VARCHAR(45) NULL,
  `legalName` VARCHAR(45) NULL,
  `ein` VARCHAR(45) NULL,
  `phone` VARCHAR(45) NULL,
  `fax` VARCHAR(45) NULL,
  `tollFree` VARCHAR(45) NULL,
  `website` VARCHAR(255) NULL,
  `email` VARCHAR(255) NULL,
  `emailStoreCorrespondence` VARCHAR(255) NULL,
  `bio` TEXT NULL,
  `latitude` VARCHAR(45) NULL,
  `longitude` VARCHAR(45) NULL,
  `storeToStoreSales` TINYINT(1) NULL,
  `bankruptcy` VARCHAR(45) NULL,
  `turnkey` VARCHAR(45) NULL,
  `openDate` DATETIME NULL,
  `transferDate` DATETIME NULL,
  `closeDate` DATETIME NULL,
  `terminationDate` DATETIME NULL,
  `hrsMon` VARCHAR(45) NULL,
  `hrsTue` VARCHAR(45) NULL,
  `hrsWed` VARCHAR(45) NULL,
  `hrsThu` VARCHAR(45) NULL,
  `hrsFri` VARCHAR(45) NULL,
  `hrsSat` VARCHAR(45) NULL,
  `hrsSun` VARCHAR(45) NULL,
  `facebookURL` VARCHAR(255) NULL,
  `territoryURL` VARCHAR(255) NULL,
  PRIMARY KEY (`storeID`, `brandID`),
  UNIQUE INDEX `storeID_UNIQUE` (`storeID` ASC),
  INDEX `fk_stores_brands2_idx` (`brandID` ASC),
  INDEX `fk_stores_addresses2_idx` (`addressID` ASC),
  INDEX `fk_stores_users1_idx` (`ownerID` ASC),
  INDEX `fk_stores_carts1_idx` (`cartID` ASC),
  CONSTRAINT `fk_stores_brands2`
    FOREIGN KEY (`brandID`)
    REFERENCES `brands` (`brandID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_stores_addresses2`
    FOREIGN KEY (`addressID`)
    REFERENCES `addresses` (`addressID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_stores_users1`
    FOREIGN KEY (`ownerID`)
    REFERENCES `users` (`userID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_stores_carts1`
    FOREIGN KEY (`cartID`)
    REFERENCES `carts` (`cartID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `usergroups`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `usergroups` ;

CREATE TABLE IF NOT EXISTS `usergroups` (
  `usergroupID` BIGINT(19) UNSIGNED NOT NULL AUTO_INCREMENT,
  `brandID` BIGINT(20) UNSIGNED NOT NULL,
  `storeID` BIGINT UNSIGNED NULL,
  `cartID` BIGINT UNSIGNED NULL,
  `usergroupName` VARCHAR(255) NULL DEFAULT NULL,
  `usergroupActive` TINYINT(1) NULL DEFAULT NULL,
  `parentUserGroupID` BIGINT(19) UNSIGNED NULL,
  PRIMARY KEY (`usergroupID`, `brandID`),
  UNIQUE INDEX `usergroupID_UNIQUE` (`usergroupID` ASC),
  INDEX `fk_usergroups_brands1_idx` (`brandID` ASC),
  INDEX `fk_usergroups_usergroups1_idx` (`parentUserGroupID` ASC),
  INDEX `fk_usergroups_carts1_idx` (`cartID` ASC),
  INDEX `fk_usergroups_stores1_idx` (`storeID` ASC),
  CONSTRAINT `fk_usergroups_brands1`
    FOREIGN KEY (`brandID`)
    REFERENCES `brands` (`brandID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_usergroups_usergroups1`
    FOREIGN KEY (`parentUserGroupID`)
    REFERENCES `usergroups` (`usergroupID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_usergroups_carts1`
    FOREIGN KEY (`cartID`)
    REFERENCES `carts` (`cartID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_usergroups_stores1`
    FOREIGN KEY (`storeID`)
    REFERENCES `stores` (`storeID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `users`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `users` ;

CREATE TABLE IF NOT EXISTS `users` (
  `userID` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `usergroupID` BIGINT(19) UNSIGNED NOT NULL,
  `userName` VARCHAR(45) CHARACTER SET 'latin1' COLLATE 'latin1_general_cs' NOT NULL,
  `userActive` TINYINT(1) NOT NULL,
  `userEmail` VARCHAR(255) NOT NULL,
  `passphrase` CHAR(128) CHARACTER SET 'latin1' COLLATE 'latin1_general_cs' NULL DEFAULT NULL,
  `firstName` VARCHAR(255) NULL,
  `middleName` VARCHAR(255) NULL,
  `lastName` VARCHAR(255) NULL,
  `phone` VARCHAR(45) NULL,
  `cellPhone` VARCHAR(45) NULL,
  `addressID` BIGINT UNSIGNED NULL,
  `notes` TEXT NULL,
  `protectedPrivateKey` BLOB NULL,
  PRIMARY KEY (`userID`, `usergroupID`, `userName`, `userActive`, `userEmail`),
  UNIQUE INDEX `userID_UNIQUE` (`userID` ASC),
  INDEX `fk_users_usergroups1_idx` (`usergroupID` ASC),
  INDEX `fk_users_addresses1_idx` (`addressID` ASC),
  CONSTRAINT `fk_users_usergroups1`
    FOREIGN KEY (`usergroupID`)
    REFERENCES `usergroups` (`usergroupID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_users_addresses1`
    FOREIGN KEY (`addressID`)
    REFERENCES `addresses` (`addressID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 2
DEFAULT CHARACTER SET = latin1
COLLATE = latin1_general_cs;


-- -----------------------------------------------------
-- Table `customerOrdersDeliveryHistory`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `customerOrdersDeliveryHistory` ;

CREATE TABLE IF NOT EXISTS `customerOrdersDeliveryHistory` (
  `customerOrdersDeliveryHistoryID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `orderID` BIGINT UNSIGNED NOT NULL,
  `dateReported` DATETIME NOT NULL,
  `userIDReported` BIGINT(20) UNSIGNED NULL,
  `deliveryStatusCode` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`customerOrdersDeliveryHistoryID`, `orderID`, `dateReported`),
  INDEX `fk_customer_orders_delivery_customer_orders1_idx` (`orderID` ASC),
  INDEX `fk_customer_orders_delivery_ref_delivery_status_codes1_idx` (`deliveryStatusCode` ASC),
  UNIQUE INDEX `customer_orders_delivery_id_UNIQUE` (`customerOrdersDeliveryHistoryID` ASC),
  INDEX `fk_customerOrdersDeliveryHistory_users1_idx` (`userIDReported` ASC),
  CONSTRAINT `fk_customer_orders_delivery_customer_orders1`
    FOREIGN KEY (`orderID`)
    REFERENCES `customerOrders` (`orderID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_customer_orders_delivery_ref_delivery_status_codes1`
    FOREIGN KEY (`deliveryStatusCode`)
    REFERENCES `refDeliveryStatusCodes` (`deliveryStatusCode`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_customerOrdersDeliveryHistory_users1`
    FOREIGN KEY (`userIDReported`)
    REFERENCES `users` (`userID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `customerPaymentMethods`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `customerPaymentMethods` ;

CREATE TABLE IF NOT EXISTS `customerPaymentMethods` (
  `customerID` BIGINT UNSIGNED NOT NULL,
  `paymentMethodID` BIGINT UNSIGNED NOT NULL,
  `paymentMethodCode` VARCHAR(45) NOT NULL,
  `billingAddressID` BIGINT UNSIGNED NOT NULL,
  `dateFrom` DATETIME NOT NULL,
  `dateTo` DATETIME NULL,
  PRIMARY KEY (`customerID`, `paymentMethodID`, `paymentMethodCode`, `dateFrom`),
  INDEX `fk_customer_payment_methods_ref_payment_method_types1_idx` (`paymentMethodCode` ASC),
  INDEX `fk_customer_payment_methods_payment_methods1_idx` (`paymentMethodID` ASC),
  INDEX `fk_customerPaymentMethods_addresses1_idx` (`billingAddressID` ASC),
  CONSTRAINT `fk_customer_payment_methods_customers1`
    FOREIGN KEY (`customerID`)
    REFERENCES `customers` (`customerID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_customer_payment_methods_ref_payment_method_types1`
    FOREIGN KEY (`paymentMethodCode`)
    REFERENCES `refPaymentMethodTypes` (`paymentMethodCode`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_customer_payment_methods_payment_methods1`
    FOREIGN KEY (`paymentMethodID`)
    REFERENCES `paymentMethods` (`paymentMethodID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_customerPaymentMethods_addresses1`
    FOREIGN KEY (`billingAddressID`)
    REFERENCES `addresses` (`addressID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `userSettings`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `userSettings` ;

CREATE TABLE IF NOT EXISTS `userSettings` (
  `userID` BIGINT(20) UNSIGNED NOT NULL,
  `dateRegistered` DATETIME NULL,
  `registrationIP` CHAR(46) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL,
  `lastLoginDate` DATETIME NULL,
  `lastLoginIP` CHAR(46) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL,
  `lastFailedLoginDate` DATETIME NULL,
  `lastFailedLoginIP` CHAR(46) NULL,
  `failedLoginAttempts` TINYINT(1) NULL,
  PRIMARY KEY (`userID`),
  UNIQUE INDEX `userID_UNIQUE` (`userID` ASC),
  CONSTRAINT `fk_userSettings_users1`
    FOREIGN KEY (`userID`)
    REFERENCES `users` (`userID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `customerOrderProductsDeliveryHistory`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `customerOrderProductsDeliveryHistory` ;

CREATE TABLE IF NOT EXISTS `customerOrderProductsDeliveryHistory` (
  `customerOrderProductsDeliveryHistoryID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `orderID` BIGINT UNSIGNED NOT NULL,
  `productID` BIGINT UNSIGNED NOT NULL,
  `dateReported` DATETIME NOT NULL,
  `userIDReported` BIGINT(20) UNSIGNED NULL,
  `deliveryStatusCode` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`customerOrderProductsDeliveryHistoryID`, `orderID`, `productID`, `dateReported`),
  INDEX `fk_customer_order_products_delivery_customer_order_products_idx` (`orderID` ASC, `productID` ASC),
  INDEX `fk_customer_order_products_delivery_ref_delivery_status_cod_idx` (`deliveryStatusCode` ASC),
  UNIQUE INDEX `customer_order_products_delivery_UNIQUE` (`customerOrderProductsDeliveryHistoryID` ASC),
  INDEX `fk_customerOrderProductsDeliveryHistory_users1_idx` (`userIDReported` ASC),
  CONSTRAINT `fk_customer_order_products_delivery_customer_order_products1`
    FOREIGN KEY (`orderID` , `productID`)
    REFERENCES `customerOrderProducts` (`orderID` , `productID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_customer_order_products_delivery_ref_delivery_status_codes1`
    FOREIGN KEY (`deliveryStatusCode`)
    REFERENCES `refDeliveryStatusCodes` (`deliveryStatusCode`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_customerOrderProductsDeliveryHistory_users1`
    FOREIGN KEY (`userIDReported`)
    REFERENCES `users` (`userID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `customerOrdersStatusHistory`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `customerOrdersStatusHistory` ;

CREATE TABLE IF NOT EXISTS `customerOrdersStatusHistory` (
  `customerOrdersStatusHistoryID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `orderID` BIGINT UNSIGNED NOT NULL,
  `dateReported` DATETIME NOT NULL,
  `userIDReported` BIGINT(20) UNSIGNED NULL,
  `orderStatusCode` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`customerOrdersStatusHistoryID`, `orderID`, `dateReported`),
  INDEX `fk_customer_orders_payment_ref_order_status_codes1_idx` (`orderStatusCode` ASC),
  UNIQUE INDEX `customer_orders_payment_id_UNIQUE` (`customerOrdersStatusHistoryID` ASC),
  INDEX `fk_customerOrdersStatusHistory_users1_idx` (`userIDReported` ASC),
  CONSTRAINT `fk_customer_orders_payment_customer_orders1`
    FOREIGN KEY (`orderID`)
    REFERENCES `customerOrders` (`orderID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_customer_orders_payment_ref_order_status_codes1`
    FOREIGN KEY (`orderStatusCode`)
    REFERENCES `refOrderStatusCodes` (`orderStatusCode`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_customerOrdersStatusHistory_users1`
    FOREIGN KEY (`userIDReported`)
    REFERENCES `users` (`userID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mercuryPayHistory`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mercuryPayHistory` ;

CREATE TABLE IF NOT EXISTS `mercuryPayHistory` (
  `mercuryPayHistoryID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `orderID` BIGINT UNSIGNED NOT NULL,
  `customerID` BIGINT UNSIGNED NOT NULL,
  `paymentMethodID` BIGINT UNSIGNED NOT NULL,
  `dateReported` DATETIME NOT NULL,
  `action` VARCHAR(45) NULL,
  `amount` DECIMAL(13,2) NULL,
  `returnCode` VARCHAR(45) NULL,
  `returnStatus` VARCHAR(45) NULL,
  `returnTextResponse` VARCHAR(255) NULL,
  `returnMessage` VARCHAR(255) NULL,
  `avsResult` VARCHAR(45) NULL,
  `cvvResult` VARCHAR(45) NULL,
  `authCode` VARCHAR(45) NULL,
  `acqRefData` VARCHAR(200) NULL,
  `refNo` VARCHAR(45) NULL,
  `processData` VARCHAR(200) NULL,
  PRIMARY KEY (`mercuryPayHistoryID`, `orderID`, `customerID`, `paymentMethodID`),
  UNIQUE INDEX `mercuryPayHistoryID_UNIQUE` (`mercuryPayHistoryID` ASC),
  INDEX `fk_mercuryPayHistory_customerOrders1_idx` (`orderID` ASC, `customerID` ASC),
  INDEX `fk_mph_pID_cpm_pID_idx` (`paymentMethodID` ASC),
  CONSTRAINT `fk_mercuryPayHistory_customerOrders1`
    FOREIGN KEY (`orderID`)
    REFERENCES `customerOrders` (`orderID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_mph_pID_cpm_pID`
    FOREIGN KEY (`paymentMethodID`)
    REFERENCES `customerPaymentMethods` (`paymentMethodID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mercuryPayCustomerPaymentMethods`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mercuryPayCustomerPaymentMethods` ;

CREATE TABLE IF NOT EXISTS `mercuryPayCustomerPaymentMethods` (
  `customerID` BIGINT UNSIGNED NOT NULL,
  `paymentMethodID` BIGINT UNSIGNED NOT NULL,
  `frequency` VARCHAR(45) NULL,
  `recordNo` VARCHAR(100) NULL,
  `dateLastUsed` DATETIME NULL,
  PRIMARY KEY (`customerID`, `paymentMethodID`),
  CONSTRAINT `fk_mercuryPayCustomerPaymentMethods_customerPaymentMethods1`
    FOREIGN KEY (`customerID` , `paymentMethodID`)
    REFERENCES `customerPaymentMethods` (`customerID` , `paymentMethodID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `paymentGateways`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `paymentGateways` ;

CREATE TABLE IF NOT EXISTS `paymentGateways` (
  `gatewayID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `gatewayName` VARCHAR(45) NULL,
  `gatewayURL` VARCHAR(255) NULL,
  `gatewayUsername` VARCHAR(255) NULL,
  `gatewayPassphrase` VARCHAR(255) NULL,
  `gatewayNotes` TEXT NULL,
  `gatewayTemplate` VARCHAR(255) NULL,
  `gatewayOffline` TINYINT(1) NULL,
  PRIMARY KEY (`gatewayID`),
  UNIQUE INDEX `gatewayID_UNIQUE` (`gatewayID` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `cartsPaymentGateways`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `cartsPaymentGateways` ;

CREATE TABLE IF NOT EXISTS `cartsPaymentGateways` (
  `cartID` BIGINT UNSIGNED NOT NULL,
  `gatewayID` BIGINT UNSIGNED NOT NULL,
  `gatewayActive` TINYINT(1) NULL,
  PRIMARY KEY (`cartID`, `gatewayID`),
  INDEX `fk_storesPaymentGateways_paymentGateways1_idx` (`gatewayID` ASC),
  CONSTRAINT `fk_storesPaymentGateways_stores1`
    FOREIGN KEY (`cartID`)
    REFERENCES `carts` (`cartID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_storesPaymentGateways_paymentGateways1`
    FOREIGN KEY (`gatewayID`)
    REFERENCES `paymentGateways` (`gatewayID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `cartsTaxRates`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `cartsTaxRates` ;

CREATE TABLE IF NOT EXISTS `cartsTaxRates` (
  `cartID` BIGINT UNSIGNED NOT NULL,
  `stateCode` VARCHAR(10) NULL,
  `stateLabel` VARCHAR(255) NULL,
  `stateTaxRate` DECIMAL(5,2) NULL,
  PRIMARY KEY (`cartID`, `stateCode`),
  CONSTRAINT `fk_storesTaxRates_stores1`
    FOREIGN KEY (`cartID`)
    REFERENCES `carts` (`cartID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `messageLog`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `messageLog` ;

CREATE TABLE IF NOT EXISTS `messageLog` (
  `messageID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `brandID` BIGINT(20) UNSIGNED NULL,
  `messageDateTime` DATETIME NULL,
  `messageCartID` BIGINT UNSIGNED NULL,
  `messageType` VARCHAR(45) NULL,
  `messageBody` TEXT NULL,
  `messageFile` VARCHAR(1000) NULL,
  `messageLine` VARCHAR(45) NULL,
  PRIMARY KEY (`messageID`),
  UNIQUE INDEX `messageID_UNIQUE` (`messageID` ASC),
  INDEX `fk_messageLog_stores1_idx` (`messageCartID` ASC),
  INDEX `fk_messageLog_brands1_idx` (`brandID` ASC),
  CONSTRAINT `fk_messageLog_stores1`
    FOREIGN KEY (`messageCartID`)
    REFERENCES `carts` (`cartID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_messageLog_brands1`
    FOREIGN KEY (`brandID`)
    REFERENCES `brands` (`brandID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `images`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `images` ;

CREATE TABLE IF NOT EXISTS `images` (
  `imageID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `parentImageID` BIGINT UNSIGNED NULL,
  `imageActive` TINYINT(1) NOT NULL,
  `imageName` VARCHAR(255) NOT NULL,
  `imageExt` VARCHAR(4) NOT NULL,
  `imageWidth` INT NULL,
  `imageHeight` INT NULL,
  `imageOrientation` VARCHAR(45) NULL,
  `imageDateCreated` DATETIME NULL,
  `brandID` BIGINT(20) UNSIGNED NULL,
  `storeID` BIGINT UNSIGNED NULL,
  `cartID` BIGINT UNSIGNED NULL,
  `userID` BIGINT(20) UNSIGNED NULL,
  `customerID` BIGINT UNSIGNED NULL,
  `categoryID` BIGINT UNSIGNED NULL,
  `productID` BIGINT UNSIGNED NULL,
  `imageSortOrder` INT NULL,
  `showInCarousel` TINYINT(1) NULL,
  `showInCarouselThumbs` TINYINT(1) NULL DEFAULT 1,
  PRIMARY KEY (`imageID`),
  INDEX `fk_stores1_idx` (`cartID` ASC),
  INDEX `fk_products1_idx` (`productID` ASC),
  INDEX `fk_customers1_idx` (`customerID` ASC),
  INDEX `fk_productCategories1_idx` (`categoryID` ASC),
  UNIQUE INDEX `imageID_UNIQUE` (`imageID` ASC),
  INDEX `fk_images_images1_idx` (`parentImageID` ASC),
  INDEX `fk_images_brands1_idx` (`brandID` ASC),
  INDEX `fk_images_stores1_idx` (`storeID` ASC),
  INDEX `fk_images_users1_idx` (`userID` ASC),
  CONSTRAINT `fk_stores1`
    FOREIGN KEY (`cartID`)
    REFERENCES `carts` (`cartID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_products1`
    FOREIGN KEY (`productID`)
    REFERENCES `products` (`productID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_customers1`
    FOREIGN KEY (`customerID`)
    REFERENCES `customers` (`customerID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_productCategories1`
    FOREIGN KEY (`categoryID`)
    REFERENCES `productCategories` (`categoryID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_images_images1`
    FOREIGN KEY (`parentImageID`)
    REFERENCES `images` (`imageID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_images_brands1`
    FOREIGN KEY (`brandID`)
    REFERENCES `brands` (`brandID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_images_stores1`
    FOREIGN KEY (`storeID`)
    REFERENCES `stores` (`storeID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_images_users1`
    FOREIGN KEY (`userID`)
    REFERENCES `users` (`userID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `productOptionsChoices`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `productOptionsChoices` ;

CREATE TABLE IF NOT EXISTS `productOptionsChoices` (
  `choiceID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `optionID` BIGINT UNSIGNED NOT NULL,
  `choiceValue` TEXT NULL,
  `choicePrice` DECIMAL(13,2) NULL,
  `choiceActive` TINYINT(1) NULL,
  `choiceImageID` BIGINT UNSIGNED NULL,
  `choiceSortOrder` TINYINT(10) NULL,
  PRIMARY KEY (`choiceID`, `optionID`),
  UNIQUE INDEX `settingID_UNIQUE` (`choiceID` ASC),
  INDEX `fk_productOptionsDetails_productOptions1_idx` (`optionID` ASC),
  INDEX `fk_productOptionsDetails_images1_idx` (`choiceImageID` ASC),
  CONSTRAINT `fk_productOptionsDetails_productOptions1`
    FOREIGN KEY (`optionID`)
    REFERENCES `productOptions` (`optionID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_productOptionsDetails_images1`
    FOREIGN KEY (`choiceImageID`)
    REFERENCES `images` (`imageID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `productOptionsCustom`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `productOptionsCustom` ;

CREATE TABLE IF NOT EXISTS `productOptionsCustom` (
  `optionIDCustom` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `productID` BIGINT UNSIGNED NOT NULL,
  `optionIDGlobal` BIGINT UNSIGNED NOT NULL,
  `optionNameCustom` VARCHAR(255) NOT NULL,
  `optionActiveCustom` TINYINT(1) NOT NULL,
  `optionBehaviorCustom` VARCHAR(45) NULL,
  `optionRequiredCustom` TINYINT(1) NULL,
  `optionSortOrder` TINYINT(10) NULL,
  `inheritsGlobalOption` TINYINT(1) NULL,
  INDEX `fk_refProductOptionsProducts_products1_idx` (`productID` ASC),
  INDEX `fk_refProductOptionsProducts_productOptions1_idx` (`optionIDGlobal` ASC),
  PRIMARY KEY (`optionIDCustom`),
  UNIQUE INDEX `refProductOptionsProductsID_UNIQUE` (`optionIDCustom` ASC),
  CONSTRAINT `fk_refProductOptionsProducts_products1`
    FOREIGN KEY (`productID`)
    REFERENCES `products` (`productID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_refProductOptionsProducts_productOptions1`
    FOREIGN KEY (`optionIDGlobal`)
    REFERENCES `productOptions` (`optionID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `productOptionsChoicesCustom`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `productOptionsChoicesCustom` ;

CREATE TABLE IF NOT EXISTS `productOptionsChoicesCustom` (
  `choiceIDCustom` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `optionIDCustom` BIGINT UNSIGNED NOT NULL,
  `choiceIDGlobal` BIGINT UNSIGNED NULL,
  `choiceValueCustom` TEXT NULL,
  `choicePriceCustom` DECIMAL(13,2) NULL,
  `choiceActiveCustom` TINYINT(1) NULL,
  `choiceImageIDCustom` BIGINT UNSIGNED NULL,
  `choiceSortOrderCustom` TINYINT(10) NULL,
  PRIMARY KEY (`choiceIDCustom`, `optionIDCustom`),
  UNIQUE INDEX `productOptionChoiceID_UNIQUE` (`choiceIDCustom` ASC),
  INDEX `fk_refProductOptionsProductsChoices_productOptionsChoices1_idx` (`choiceIDGlobal` ASC),
  INDEX `fk_productOptionsChoicesCustom_productOptionsCustom1_idx` (`optionIDCustom` ASC),
  INDEX `fk_productOptionsChoicesCustom_images1_idx` (`choiceImageIDCustom` ASC),
  CONSTRAINT `fk_refProductOptionsProductsChoices_productOptionsChoices1`
    FOREIGN KEY (`choiceIDGlobal`)
    REFERENCES `productOptionsChoices` (`choiceID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_productOptionsChoicesCustom_productOptionsCustom1`
    FOREIGN KEY (`optionIDCustom`)
    REFERENCES `productOptionsCustom` (`optionIDCustom`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_productOptionsChoicesCustom_images1`
    FOREIGN KEY (`choiceImageIDCustom`)
    REFERENCES `images` (`imageID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `customerOrderProductsOptions`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `customerOrderProductsOptions` ;

CREATE TABLE IF NOT EXISTS `customerOrderProductsOptions` (
  `copoID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `orderID` BIGINT UNSIGNED NOT NULL,
  `productID` BIGINT UNSIGNED NOT NULL,
  `productOptionCustomID` BIGINT UNSIGNED NOT NULL,
  `productOptionChoiceCustomID` BIGINT UNSIGNED NOT NULL,
  `optionValue` TEXT NULL,
  INDEX `fk_customerOrderProductsOptions_customerOrderProducts1_idx` (`orderID` ASC),
  INDEX `fk_customerOrderProductsOptions_products1_idx` (`productID` ASC),
  PRIMARY KEY (`copoID`),
  UNIQUE INDEX `copoID_UNIQUE` (`copoID` ASC),
  INDEX `fk_customerOrderProductsOptions_refProductOptionsProducts1_idx` (`productOptionCustomID` ASC),
  INDEX `fk_customerOrderProductsOptions_productOptionsChoicesCustom_idx` (`productOptionChoiceCustomID` ASC),
  CONSTRAINT `fk_customerOrderProductsOptions_customerOrderProducts1`
    FOREIGN KEY (`orderID`)
    REFERENCES `customerOrderProducts` (`orderID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_customerOrderProductsOptions_products1`
    FOREIGN KEY (`productID`)
    REFERENCES `products` (`productID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_customerOrderProductsOptions_refProductOptionsProducts1`
    FOREIGN KEY (`productOptionCustomID`)
    REFERENCES `productOptionsCustom` (`optionIDCustom`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_customerOrderProductsOptions_productOptionsChoicesCustom1`
    FOREIGN KEY (`productOptionChoiceCustomID`)
    REFERENCES `productOptionsChoicesCustom` (`choiceIDCustom`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `customerUserExchange`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `customerUserExchange` ;

CREATE TABLE IF NOT EXISTS `customerUserExchange` (
  `sessionName` VARCHAR(255) NOT NULL,
  `sessionValue` VARCHAR(255) NOT NULL,
  `authenticated` TINYINT(1) NULL,
  `existingUser` VARCHAR(255) NULL,
  `cartCustomerID` BIGINT UNSIGNED NULL,
  `sessionExpires` DATETIME NULL,
  `consumed` TINYINT(1) NULL,
  PRIMARY KEY (`sessionName`, `sessionValue`),
  UNIQUE INDEX `sessionValue_UNIQUE` (`sessionValue` ASC),
  INDEX `fk_customerUserExchangeHistory_customers1_idx` (`cartCustomerID` ASC),
  CONSTRAINT `fk_customerUserExchangeHistory_customers1`
    FOREIGN KEY (`cartCustomerID`)
    REFERENCES `customers` (`customerID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `refProductOptionsChoices`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `refProductOptionsChoices` ;

CREATE TABLE IF NOT EXISTS `refProductOptionsChoices` (
  `parentOptionID` BIGINT UNSIGNED NOT NULL,
  `childOptionID` BIGINT UNSIGNED NOT NULL,
  `triggerChoiceID` BIGINT UNSIGNED NULL,
  INDEX `fk_refProductOptionsChoices_productOptions1_idx` (`parentOptionID` ASC),
  INDEX `fk_refProductOptionsChoices_productOptionsDetails1_idx` (`triggerChoiceID` ASC),
  INDEX `fk_refProductOptionsChoices_productOptions2_idx` (`childOptionID` ASC),
  CONSTRAINT `fk_refProductOptionsChoices_productOptions1`
    FOREIGN KEY (`parentOptionID`)
    REFERENCES `productOptions` (`optionID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_refProductOptionsChoices_productOptionsDetails1`
    FOREIGN KEY (`triggerChoiceID`)
    REFERENCES `productOptionsChoices` (`choiceID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_refProductOptionsChoices_productOptions2`
    FOREIGN KEY (`childOptionID`)
    REFERENCES `productOptions` (`optionID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `refProductOptionsChoicesCustom`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `refProductOptionsChoicesCustom` ;

CREATE TABLE IF NOT EXISTS `refProductOptionsChoicesCustom` (
  `parentOptionIDCustom` BIGINT UNSIGNED NOT NULL,
  `childOptionIDCustom` BIGINT UNSIGNED NOT NULL,
  `triggerChoiceIDCustom` BIGINT UNSIGNED NULL,
  INDEX `fk_refProductOptionsChoicesCustom_productOptionsCustom2_idx` (`childOptionIDCustom` ASC),
  INDEX `fk_refProductOptionsChoicesCustom_productOptionsChoicesCust_idx` (`triggerChoiceIDCustom` ASC),
  CONSTRAINT `fk_refProductOptionsChoicesCustom_productOptionsCustom1`
    FOREIGN KEY (`parentOptionIDCustom`)
    REFERENCES `productOptionsCustom` (`optionIDCustom`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_refProductOptionsChoicesCustom_productOptionsCustom2`
    FOREIGN KEY (`childOptionIDCustom`)
    REFERENCES `productOptionsCustom` (`optionIDCustom`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_refProductOptionsChoicesCustom_productOptionsChoicesCustom1`
    FOREIGN KEY (`triggerChoiceIDCustom`)
    REFERENCES `productOptionsChoicesCustom` (`choiceIDCustom`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `storefrontCarousel`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `storefrontCarousel` ;

CREATE TABLE IF NOT EXISTS `storefrontCarousel` (
  `cartID` BIGINT UNSIGNED NOT NULL,
  `interval` TINYINT(1) NULL,
  `slide1ImageID` BIGINT UNSIGNED NULL,
  `slide1Title` VARCHAR(255) NULL,
  `slide1Description` TEXT NULL,
  `slide1URL` VARCHAR(255) NULL,
  `slide2ImageID` BIGINT UNSIGNED NULL,
  `slide2Title` VARCHAR(255) NULL,
  `slide2Description` TEXT NULL,
  `slide2URL` VARCHAR(255) NULL,
  `slide3ImageID` BIGINT UNSIGNED NULL,
  `slide3Title` VARCHAR(255) NULL,
  `slide3Description` TEXT NULL,
  `slide3URL` VARCHAR(255) NULL,
  `slide4ImageID` BIGINT UNSIGNED NULL,
  `slide4Title` VARCHAR(255) NULL,
  `slide4Description` TEXT NULL,
  `slide4URL` VARCHAR(255) NULL,
  `slide5ImageID` BIGINT UNSIGNED NULL,
  `slide5Title` VARCHAR(255) NULL,
  `slide5Description` TEXT NULL,
  `slide5URL` VARCHAR(255) NULL,
  PRIMARY KEY (`cartID`),
  INDEX `fk_storefrontCarousel_images1_idx` (`slide1ImageID` ASC),
  INDEX `fk_storefrontCarousel_images2_idx` (`slide2ImageID` ASC),
  INDEX `fk_storefrontCarousel_images3_idx` (`slide3ImageID` ASC),
  INDEX `fk_storefrontCarousel_images4_idx` (`slide4ImageID` ASC),
  INDEX `fk_storefrontCarousel_images5_idx` (`slide5ImageID` ASC),
  UNIQUE INDEX `storeID_UNIQUE` (`cartID` ASC),
  CONSTRAINT `fk_storefrontCarousel_stores1`
    FOREIGN KEY (`cartID`)
    REFERENCES `carts` (`cartID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_storefrontCarousel_images1`
    FOREIGN KEY (`slide1ImageID`)
    REFERENCES `images` (`imageID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_storefrontCarousel_images2`
    FOREIGN KEY (`slide2ImageID`)
    REFERENCES `images` (`imageID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_storefrontCarousel_images3`
    FOREIGN KEY (`slide3ImageID`)
    REFERENCES `images` (`imageID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_storefrontCarousel_images4`
    FOREIGN KEY (`slide4ImageID`)
    REFERENCES `images` (`imageID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_storefrontCarousel_images5`
    FOREIGN KEY (`slide5ImageID`)
    REFERENCES `images` (`imageID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `articles`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `articles` ;

CREATE TABLE IF NOT EXISTS `articles` (
  `articleID` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `articleName` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL DEFAULT NULL,
  `articleActive` TINYINT(1) UNSIGNED NOT NULL,
  `articleShortDescription` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL DEFAULT NULL,
  `articleLongDescription` TEXT CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL DEFAULT NULL,
  `articleDatePublished` DATETIME NOT NULL,
  `articleDateLastReviewed` DATETIME NOT NULL,
  `articleDateLastUpdated` DATETIME NOT NULL,
  `articleDateExpires` DATETIME NULL DEFAULT NULL,
  `userID` BIGINT(20) UNSIGNED NOT NULL,
  `brandID` BIGINT(20) UNSIGNED NOT NULL,
  PRIMARY KEY (`articleID`, `articleActive`, `brandID`, `userID`),
  UNIQUE INDEX `articleID_UNIQUE` (`articleID` ASC),
  INDEX `fk_articles_users1_idx` (`userID` ASC),
  INDEX `fk_articles_brands1_idx` (`brandID` ASC),
  CONSTRAINT `fk_articles_users1`
    FOREIGN KEY (`userID`)
    REFERENCES `users` (`userID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_articles_brands1`
    FOREIGN KEY (`brandID`)
    REFERENCES `brands` (`brandID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 12
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `refParentItemTypes`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `refParentItemTypes` ;

CREATE TABLE IF NOT EXISTS `refParentItemTypes` (
  `refParentItemTypeID` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `parentTypeLabel` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL DEFAULT NULL,
  PRIMARY KEY (`refParentItemTypeID`),
  UNIQUE INDEX `refParentItemTypeID_UNIQUE` (`refParentItemTypeID` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 4
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `bodyContents`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `bodyContents` ;

CREATE TABLE IF NOT EXISTS `bodyContents` (
  `bodyContentID` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `parentItemID` BIGINT(20) UNSIGNED NOT NULL,
  `parentItemTypeID` BIGINT(20) UNSIGNED NOT NULL,
  `bodyContentActive` TINYINT(1) UNSIGNED NOT NULL,
  `bodyContentDateModified` DATETIME NOT NULL,
  `bodyContentText` LONGTEXT CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL DEFAULT NULL,
  `userID` BIGINT(20) UNSIGNED NOT NULL,
  PRIMARY KEY (`bodyContentID`, `parentItemID`, `bodyContentActive`, `parentItemTypeID`, `userID`),
  UNIQUE INDEX `commentBodyID_UNIQUE` (`bodyContentID` ASC),
  INDEX `fk_bodyContents_refParentItemTypes1_idx` (`parentItemTypeID` ASC),
  INDEX `fk_bodyContents_users1_idx` (`userID` ASC),
  CONSTRAINT `fk_bodyContents_refParentItemTypes1`
    FOREIGN KEY (`parentItemTypeID`)
    REFERENCES `refParentItemTypes` (`refParentItemTypeID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_bodyContents_users1`
    FOREIGN KEY (`userID`)
    REFERENCES `users` (`userID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 18
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `categories`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `categories` ;

CREATE TABLE IF NOT EXISTS `categories` (
  `categoryID` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `categoryName` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL DEFAULT NULL,
  `categoryActive` TINYINT(1) UNSIGNED NOT NULL,
  PRIMARY KEY (`categoryID`, `categoryActive`),
  UNIQUE INDEX `categoryID_UNIQUE` (`categoryID` ASC))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `domains`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `domains` ;

CREATE TABLE IF NOT EXISTS `domains` (
  `domainID` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `brandID` BIGINT(20) UNSIGNED NOT NULL,
  `domainName` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  `domainActive` TINYINT(1) UNSIGNED NOT NULL,
  `parentDomainID` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
  `storeID` BIGINT UNSIGNED NULL,
  PRIMARY KEY (`domainID`, `brandID`, `domainName`, `domainActive`),
  UNIQUE INDEX `domainID_UNIQUE` (`domainID` ASC),
  INDEX `fk_domains_domains1_idx` (`parentDomainID` ASC),
  INDEX `fk_domains_brands1_idx` (`brandID` ASC),
  INDEX `fk_domains_stores1_idx` (`storeID` ASC),
  CONSTRAINT `fk_domains_domains1`
    FOREIGN KEY (`parentDomainID`)
    REFERENCES `domains` (`domainID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_domains_brands1`
    FOREIGN KEY (`brandID`)
    REFERENCES `brands` (`brandID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_domains_stores1`
    FOREIGN KEY (`storeID`)
    REFERENCES `stores` (`storeID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `keywords`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `keywords` ;

CREATE TABLE IF NOT EXISTS `keywords` (
  `keywordID` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `keyword` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
  PRIMARY KEY (`keywordID`, `keyword`),
  UNIQUE INDEX `keywordID_UNIQUE` (`keywordID` ASC))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci
COMMENT = '<double-click to overwrite multiple objects>';


-- -----------------------------------------------------
-- Table `refCategories`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `refCategories` ;

CREATE TABLE IF NOT EXISTS `refCategories` (
  `parentItemID` BIGINT(20) UNSIGNED NOT NULL,
  `parentItemTypeID` BIGINT(20) UNSIGNED NOT NULL,
  `categoryID` BIGINT(20) UNSIGNED NOT NULL,
  PRIMARY KEY (`parentItemID`, `categoryID`, `parentItemTypeID`),
  INDEX `fk_table1_Categories1_idx` (`categoryID` ASC),
  INDEX `fk_refCategories_refParentItemTypes1_idx` (`parentItemTypeID` ASC),
  CONSTRAINT `fk_refCategories_refParentItemTypes1`
    FOREIGN KEY (`parentItemTypeID`)
    REFERENCES `refParentItemTypes` (`refParentItemTypeID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_table1_Categories1`
    FOREIGN KEY (`categoryID`)
    REFERENCES `categories` (`categoryID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `refImages`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `refImages` ;

CREATE TABLE IF NOT EXISTS `refImages` (
  `parentItemID` BIGINT(20) UNSIGNED NOT NULL,
  `parentItemTypeID` BIGINT(20) UNSIGNED NOT NULL,
  `imageID` BIGINT(20) UNSIGNED NOT NULL,
  PRIMARY KEY (`parentItemID`, `imageID`, `parentItemTypeID`),
  INDEX `fk_refArticlesImages_Images1_idx` (`imageID` ASC),
  INDEX `fk_refArticlesImages_refParentItemTypes1_idx` (`parentItemTypeID` ASC),
  CONSTRAINT `fk_refArticlesImages_Images1`
    FOREIGN KEY (`imageID`)
    REFERENCES `images` (`imageID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_refArticlesImages_refParentItemTypes1`
    FOREIGN KEY (`parentItemTypeID`)
    REFERENCES `refParentItemTypes` (`refParentItemTypeID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `refKeywords`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `refKeywords` ;

CREATE TABLE IF NOT EXISTS `refKeywords` (
  `parentItemID` BIGINT(20) UNSIGNED NOT NULL,
  `parentItemTypeID` BIGINT(20) UNSIGNED NOT NULL,
  `keywordID` BIGINT(20) UNSIGNED NOT NULL,
  `keywordCount` INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (`parentItemID`, `parentItemTypeID`, `keywordID`),
  INDEX `fk_refParentItemKeywords_keywords1_idx` (`keywordID` ASC),
  INDEX `fk_refParentItemKeywords_refParentItemTypes1_idx` (`parentItemTypeID` ASC),
  CONSTRAINT `fk_refParentItemKeywords_keywords1`
    FOREIGN KEY (`keywordID`)
    REFERENCES `keywords` (`keywordID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_refParentItemKeywords_refParentItemTypes1`
    FOREIGN KEY (`parentItemTypeID`)
    REFERENCES `refParentItemTypes` (`refParentItemTypeID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `pages`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pages` ;

CREATE TABLE IF NOT EXISTS `pages` (
  `pageID` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `pageName` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL DEFAULT NULL,
  `pageActive` TINYINT(1) UNSIGNED NOT NULL,
  `pagePrivateName` varchar(255) DEFAULT NULL,
  `pageHeading` varchar(255) DEFAULT NULL,
  `pageMetaTitle` varchar(255) DEFAULT NULL,
  `pageMetaDescription` text,
  `pageMetaKeywords` varchar(255) DEFAULT NULL,  
  `pageDatePublished` DATETIME NOT NULL,
  `pageDateLastReviewed` DATETIME NOT NULL,
  `pageDateLastUpdated` DATETIME NOT NULL,
  `pageDateExpires` DATETIME NULL DEFAULT NULL,
  `userID` BIGINT(20) UNSIGNED NOT NULL,
  `brandID` BIGINT(20) UNSIGNED NOT NULL,
  `pageJavaScript` TEXT NULL DEFAULT NULL,
  `pageRestricted` TINYINT(1) NULL,
  PRIMARY KEY (`pageID`, `pageActive`, `brandID`, `userID`),
  UNIQUE INDEX `articleID_UNIQUE` (`pageID` ASC),
  INDEX `fk_articles_users1_idx` (`userID` ASC),
  INDEX `fk_pages_brands1_idx` (`brandID` ASC),
  CONSTRAINT `fk_pages_users10`
    FOREIGN KEY (`userID`)
    REFERENCES `users` (`userID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_pages_brands1`
    FOREIGN KEY (`brandID`)
    REFERENCES `brands` (`brandID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 12
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `acls`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `acls` ;

CREATE TABLE IF NOT EXISTS `acls` (
  `brandID` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
  `usergroupID` BIGINT(19) UNSIGNED NULL DEFAULT NULL,
  `userID` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
  `controller` VARCHAR(45) NULL DEFAULT NULL,
  `create` TINYINT(1) NULL DEFAULT NULL,
  `read` TINYINT(1) NULL DEFAULT NULL,
  `update` TINYINT(1) NULL DEFAULT NULL,
  `delete` TINYINT(1) NULL DEFAULT NULL,
  INDEX `fk_aclDefaults_usergroups1_idx` (`usergroupID` ASC),
  INDEX `fk_aclDefaults_users1_idx` (`userID` ASC),
  INDEX `fk_aclDefaults_brands10` (`brandID` ASC),
  CONSTRAINT `fk_aclDefaults_brands10`
    FOREIGN KEY (`brandID`)
    REFERENCES `brands` (`brandID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_aclDefaults_usergroups10`
    FOREIGN KEY (`usergroupID`)
    REFERENCES `usergroups` (`usergroupID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_aclDefaults_users10`
    FOREIGN KEY (`userID`)
    REFERENCES `users` (`userID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `menus`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `menus` ;

CREATE TABLE IF NOT EXISTS `menus` (
  `menuID` BIGINT(19) UNSIGNED NOT NULL AUTO_INCREMENT,
  `brandID` BIGINT(20) UNSIGNED NOT NULL,
  `menuType` VARCHAR(45) NULL,
  `menuName` VARCHAR(45) NULL DEFAULT NULL,
  `menuTitle` VARCHAR(255) NULL DEFAULT NULL,
  `menuRestricted` TINYINT(1) NULL,
  `menuActive` TINYINT(1) NULL DEFAULT NULL,
  PRIMARY KEY (`menuID`, `brandID`),
  UNIQUE INDEX `ID_UNIQUE` (`menuID` ASC),
  INDEX `fk_menus_brands2_idx` (`brandID` ASC),
  CONSTRAINT `fk_menus_brands2`
    FOREIGN KEY (`brandID`)
    REFERENCES `brands` (`brandID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `menuLinks`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `menuLinks` ;

CREATE TABLE IF NOT EXISTS `menuLinks` (
  `linkID` BIGINT(19) UNSIGNED NOT NULL AUTO_INCREMENT,
  `menuID` BIGINT(19) UNSIGNED NOT NULL,
  `sortOrder` TINYINT(2) NULL DEFAULT NULL,
  `parentLinkID` BIGINT(19) UNSIGNED NULL DEFAULT NULL,
  `display` VARCHAR(45) NULL DEFAULT NULL,
  `url` VARCHAR(255) NULL DEFAULT NULL,
  `linkActive` TINYINT(1) NULL DEFAULT NULL,
  PRIMARY KEY (`linkID`, `menuID`),
  UNIQUE INDEX `ID_UNIQUE` (`linkID` ASC),
  INDEX `fk_menus_menus1_idx` (`parentLinkID` ASC),
  INDEX `fk_menuLinks_menus1_idx` (`menuID` ASC),
  CONSTRAINT `fk_menus_menus1`
    FOREIGN KEY (`parentLinkID`)
    REFERENCES `menuLinks` (`linkID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_menuLinks_menus1`
    FOREIGN KEY (`menuID`)
    REFERENCES `menus` (`menuID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `rewriteMappings`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `rewriteMappings` ;

CREATE TABLE IF NOT EXISTS `rewriteMappings` (
  `rewriteMappingID` BIGINT(19) UNSIGNED NOT NULL AUTO_INCREMENT,
  `aliasURL` VARCHAR(255) NULL DEFAULT NULL,
  `actualURL` VARCHAR(255) NULL DEFAULT NULL,
  `sortOrder` INT(11) NULL DEFAULT NULL,
  `domainID` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
  PRIMARY KEY (`rewriteMappingID`),
  UNIQUE INDEX `rewriteMappingID_UNIQUE` (`rewriteMappingID` ASC),
  INDEX `fk_rewriteMappings_domains1_idx` (`domainID` ASC),
  CONSTRAINT `fk_rewriteMappings_domains1`
    FOREIGN KEY (`domainID`)
    REFERENCES `domains` (`domainID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `comments`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `comments` ;

CREATE TABLE IF NOT EXISTS `comments` (
  `commentID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `parentItemID` BIGINT NULL,
  `parentItemTypeID` BIGINT(20) UNSIGNED NOT NULL,
  `commentActive` TINYINT(1) NULL,
  `commentDatePublished` DATETIME NULL,
  `commentDateLastUpdated` DATETIME NULL,
  `userID` BIGINT(20) UNSIGNED NOT NULL,
  PRIMARY KEY (`commentID`, `userID`),
  UNIQUE INDEX `commentID_UNIQUE` (`commentID` ASC),
  INDEX `fk_comments_refParentItemTypes1_idx` (`parentItemTypeID` ASC),
  INDEX `fk_comments_users1_idx` (`userID` ASC),
  CONSTRAINT `fk_comments_refParentItemTypes1`
    FOREIGN KEY (`parentItemTypeID`)
    REFERENCES `refParentItemTypes` (`refParentItemTypeID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_comments_users1`
    FOREIGN KEY (`userID`)
    REFERENCES `users` (`userID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `services`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `services` ;

CREATE TABLE IF NOT EXISTS `services` (
  `serviceID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `brandID` BIGINT(20) UNSIGNED NOT NULL,
  `serviceName` VARCHAR(255) NULL,
  `serviceDescription` VARCHAR(255) NULL,
  `serviceActive` TINYINT(1) NULL,
  PRIMARY KEY (`serviceID`, `brandID`),
  UNIQUE INDEX `serviceID_UNIQUE` (`serviceID` ASC),
  INDEX `fk_services_brands1_idx` (`brandID` ASC),
  CONSTRAINT `fk_services_brands1`
    FOREIGN KEY (`brandID`)
    REFERENCES `brands` (`brandID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `refStoreServices`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `refStoreServices` ;

CREATE TABLE IF NOT EXISTS `refStoreServices` (
  `storeID` BIGINT UNSIGNED NOT NULL,
  `serviceID` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`storeID`, `serviceID`),
  INDEX `fk_storeServices_services1_idx` (`serviceID` ASC),
  CONSTRAINT `fk_storeServices_stores1`
    FOREIGN KEY (`storeID`)
    REFERENCES `stores` (`storeID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_storeServices_services1`
    FOREIGN KEY (`serviceID`)
    REFERENCES `services` (`serviceID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `files`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `files` ;

CREATE TABLE IF NOT EXISTS `files` (
  `fileID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `brandID` BIGINT(20) UNSIGNED NOT NULL,
  `parentFileID` BIGINT UNSIGNED NULL,
  `storeID` BIGINT UNSIGNED NULL,
  `categoryID` BIGINT UNSIGNED NULL,
  `productID` BIGINT UNSIGNED NULL,
  `userID` BIGINT(20) UNSIGNED NULL,
  `usergroupID` BIGINT(19) UNSIGNED NULL,
  `customerID` BIGINT UNSIGNED NULL,
  `isActive` TINYINT(1) NULL,
  `isDeleted` TINYINT(1) NULL,
  `sortOrder` TINYINT(10) NULL,
  `dateCreated` DATETIME NULL,
  `isPrivate` TINYINT(1) NULL,
  `type` VARCHAR(45) NULL,
  `ext` VARCHAR(5) NULL,
  `imgWidth` INT NULL,
  `imgHeight` INT NULL,
  `imgOrient` VARCHAR(45) NULL,
  `label` VARCHAR(45) NULL,
  `displayName` VARCHAR(45) NULL,
  PRIMARY KEY (`fileID`, `brandID`),
  UNIQUE INDEX `fileID_UNIQUE` (`fileID` ASC),
  INDEX `fk_files_brands1_idx` (`brandID` ASC),
  INDEX `fk_files_stores1_idx` (`storeID` ASC),
  INDEX `fk_files_productCategories1_idx` (`categoryID` ASC),
  INDEX `fk_files_products1_idx` (`productID` ASC),
  INDEX `fk_files_users1_idx` (`userID` ASC),
  INDEX `fk_files_usergroups1_idx` (`usergroupID` ASC),
  INDEX `fk_files_customers1_idx` (`customerID` ASC),
  INDEX `fk_files_files1_idx` (`parentFileID` ASC),
  CONSTRAINT `fk_files_brands1`
    FOREIGN KEY (`brandID`)
    REFERENCES `brands` (`brandID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_files_stores1`
    FOREIGN KEY (`storeID`)
    REFERENCES `stores` (`storeID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_files_productCategories1`
    FOREIGN KEY (`categoryID`)
    REFERENCES `productCategories` (`categoryID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_files_products1`
    FOREIGN KEY (`productID`)
    REFERENCES `products` (`productID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_files_users1`
    FOREIGN KEY (`userID`)
    REFERENCES `users` (`userID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_files_usergroups1`
    FOREIGN KEY (`usergroupID`)
    REFERENCES `usergroups` (`usergroupID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_files_customers1`
    FOREIGN KEY (`customerID`)
    REFERENCES `customers` (`customerID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_files_files1`
    FOREIGN KEY (`parentFileID`)
    REFERENCES `files` (`fileID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
