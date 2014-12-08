SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

use gpCart;

ALTER TABLE `products`
	ADD COLUMN `productSpecifications` TEXT NULL DEFAULT NULL  AFTER `productSKU` ,
	ADD COLUMN `productShipping` TEXT NULL DEFAULT NULL  AFTER `productSpecifications` ,
	ADD COLUMN `productWarranty` TEXT NULL DEFAULT NULL  AFTER `productShipping` ;

ALTER TABLE `stores`
	ADD COLUMN `termsOfService` TEXT NULL DEFAULT NULL  AFTER `storePublicKey` ,
	ADD COLUMN `storefrontCarousel` TINYINT(1) NULL DEFAULT NULL  AFTER `termsOfService` ,
	ADD COLUMN `storefrontCategories` TINYINT(1) NULL DEFAULT NULL  AFTER `storefrontCarousel`,
	ADD COLUMN `storefrontDescription` TEXT NULL  AFTER `storefrontCategories`;

ALTER TABLE `images`
	DROP COLUMN `imageVisibility` ,
	ADD COLUMN `showInCarousel` TINYINT(1) NULL DEFAULT NULL  AFTER `imageSortOrder` ,
	ADD COLUMN `showInCarouselThumbs` TINYINT(1) NULL DEFAULT 1  AFTER `showInCarousel` ;

ALTER TABLE `refProductOptionsChoicesCustom` 
	DROP INDEX `fk_refProductOptionsChoicesCustom_productOptionsCustom1` ;

ALTER TABLE `refStoresProductCategories` 
	DROP INDEX `fk_ref_stores_product_categories_stores1` ;

ALTER TABLE `storeGroupUsers` 
	DROP INDEX `fk_store_group_users_store_user_groups1` ;

ALTER TABLE `customerOrdersStatusHistory` 
	DROP INDEX `fk_customer_orders_payment_customer_orders1` ;

CREATE  TABLE IF NOT EXISTS `storefrontCarousel` (
  `storeID` BIGINT(19) UNSIGNED NOT NULL ,
  `interval` TINYINT(1) NULL DEFAULT NULL ,
  `slide1ImageID` BIGINT(19) UNSIGNED NULL DEFAULT NULL ,
  `slide1Title` VARCHAR(255) NULL DEFAULT NULL ,
  `slide1Description` TEXT NULL DEFAULT NULL ,
  `slide1URL` VARCHAR(255) NULL DEFAULT NULL ,
  `slide2ImageID` BIGINT(19) UNSIGNED NULL DEFAULT NULL ,
  `slide2Title` VARCHAR(255) NULL DEFAULT NULL ,
  `slide2Description` TEXT NULL DEFAULT NULL ,
  `slide2URL` VARCHAR(255) NULL DEFAULT NULL ,
  `slide3ImageID` BIGINT(19) UNSIGNED NULL DEFAULT NULL ,
  `slide3Title` VARCHAR(255) NULL DEFAULT NULL ,
  `slide3Description` TEXT NULL DEFAULT NULL ,
  `slide3URL` VARCHAR(255) NULL DEFAULT NULL ,
  `slide4ImageID` BIGINT(19) UNSIGNED NULL DEFAULT NULL ,
  `slide4Title` VARCHAR(255) NULL DEFAULT NULL ,
  `slide4Description` TEXT NULL DEFAULT NULL ,
  `slide4URL` VARCHAR(255) NULL DEFAULT NULL ,
  `slide5ImageID` BIGINT(19) UNSIGNED NULL DEFAULT NULL ,
  `slide5Title` VARCHAR(255) NULL DEFAULT NULL ,
  `slide5Description` TEXT NULL DEFAULT NULL ,
  `slide5URL` VARCHAR(255) NULL DEFAULT NULL ,
  PRIMARY KEY (`storeID`) ,
  INDEX `fk_storefrontCarousel_images1_idx` (`slide1ImageID` ASC) ,
  INDEX `fk_storefrontCarousel_images2_idx` (`slide2ImageID` ASC) ,
  INDEX `fk_storefrontCarousel_images3_idx` (`slide3ImageID` ASC) ,
  INDEX `fk_storefrontCarousel_images4_idx` (`slide4ImageID` ASC) ,
  INDEX `fk_storefrontCarousel_images5_idx` (`slide5ImageID` ASC) ,
  UNIQUE INDEX `storeID_UNIQUE` (`storeID` ASC) ,
  CONSTRAINT `fk_storefrontCarousel_stores1`
    FOREIGN KEY (`storeID` )
    REFERENCES `stores` (`storeID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_storefrontCarousel_images1`
    FOREIGN KEY (`slide1ImageID` )
    REFERENCES `images` (`imageID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_storefrontCarousel_images2`
    FOREIGN KEY (`slide2ImageID` )
    REFERENCES `images` (`imageID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_storefrontCarousel_images3`
    FOREIGN KEY (`slide3ImageID` )
    REFERENCES `images` (`imageID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_storefrontCarousel_images4`
    FOREIGN KEY (`slide4ImageID` )
    REFERENCES `images` (`imageID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_storefrontCarousel_images5`
    FOREIGN KEY (`slide5ImageID` )
    REFERENCES `images` (`imageID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
