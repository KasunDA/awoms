SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

DROP SCHEMA IF EXISTS `awoms` ;
CREATE SCHEMA IF NOT EXISTS `awoms` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ;
USE `awoms` ;

-- -----------------------------------------------------
-- Table `awoms`.`users`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `awoms`.`users` ;

CREATE  TABLE IF NOT EXISTS `awoms`.`users` (
  `userID` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `userActive` TINYINT(1) NULL DEFAULT NULL ,
  `username` VARCHAR(45) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL DEFAULT NULL ,
  `passphrase` CHAR(128) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL DEFAULT NULL ,
  PRIMARY KEY (`userID`) ,
  UNIQUE INDEX `userID_UNIQUE` (`userID` ASC) )
ENGINE = InnoDB
AUTO_INCREMENT = 2
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `awoms`.`articles`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `awoms`.`articles` ;

CREATE  TABLE IF NOT EXISTS `awoms`.`articles` (
  `articleID` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `articleName` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL DEFAULT NULL ,
  `articleActive` TINYINT(1) UNSIGNED NOT NULL ,
  `articleShortDescription` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL DEFAULT NULL ,
  `articleLongDescription` TEXT CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL DEFAULT NULL ,
  `articleDatePublished` DATETIME NOT NULL ,
  `articleDateLastReviewed` DATETIME NOT NULL ,
  `articleDateLastUpdated` DATETIME NOT NULL ,
  `articleDateExpires` DATETIME NULL DEFAULT NULL ,
  `userID` BIGINT(20) UNSIGNED NOT NULL ,
  PRIMARY KEY (`articleID`) ,
  UNIQUE INDEX `articleID_UNIQUE` (`articleID` ASC) ,
  INDEX `fk_articles_users1` (`userID` ASC) ,
  CONSTRAINT `fk_articles_users1`
    FOREIGN KEY (`userID` )
    REFERENCES `awoms`.`users` (`userID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 12
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `awoms`.`refParentItemTypes`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `awoms`.`refParentItemTypes` ;

CREATE  TABLE IF NOT EXISTS `awoms`.`refParentItemTypes` (
  `refParentItemTypeID` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `parentTypeLabel` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL DEFAULT NULL ,
  PRIMARY KEY (`refParentItemTypeID`) ,
  UNIQUE INDEX `refParentItemTypeID_UNIQUE` (`refParentItemTypeID` ASC) )
ENGINE = InnoDB
AUTO_INCREMENT = 4
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `awoms`.`bodyContents`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `awoms`.`bodyContents` ;

CREATE  TABLE IF NOT EXISTS `awoms`.`bodyContents` (
  `bodyContentID` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `parentItemID` BIGINT(20) UNSIGNED NOT NULL ,
  `parentItemTypeID` BIGINT(20) UNSIGNED NOT NULL ,
  `bodyContentActive` TINYINT(1) UNSIGNED NOT NULL ,
  `bodyContentDateModified` DATETIME NOT NULL ,
  `bodyContentText` TEXT CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL DEFAULT NULL ,
  `userID` BIGINT(20) UNSIGNED NOT NULL ,
  PRIMARY KEY (`bodyContentID`, `parentItemID`, `bodyContentActive`) ,
  UNIQUE INDEX `commentBodyID_UNIQUE` (`bodyContentID` ASC) ,
  INDEX `fk_bodyContents_refParentItemTypes1` (`parentItemTypeID` ASC) ,
  INDEX `fk_bodyContents_users1` (`userID` ASC) ,
  CONSTRAINT `fk_bodyContents_refParentItemTypes1`
    FOREIGN KEY (`parentItemTypeID` )
    REFERENCES `awoms`.`refParentItemTypes` (`refParentItemTypeID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_bodyContents_users1`
    FOREIGN KEY (`userID` )
    REFERENCES `awoms`.`users` (`userID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 18
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `awoms`.`brands`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `awoms`.`brands` ;

CREATE  TABLE IF NOT EXISTS `awoms`.`brands` (
  `brandID` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `brandName` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL ,
  `brandActive` TINYINT(1) NULL DEFAULT NULL ,
  PRIMARY KEY (`brandID`, `brandName`) ,
  UNIQUE INDEX `brandID_UNIQUE` (`brandID` ASC) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `awoms`.`categories`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `awoms`.`categories` ;

CREATE  TABLE IF NOT EXISTS `awoms`.`categories` (
  `categoryID` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `categoryName` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL DEFAULT NULL ,
  `categoryActive` TINYINT(1) NULL DEFAULT NULL ,
  PRIMARY KEY (`categoryID`) ,
  UNIQUE INDEX `categoryID_UNIQUE` (`categoryID` ASC) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `awoms`.`comments`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `awoms`.`comments` ;

CREATE  TABLE IF NOT EXISTS `awoms`.`comments` (
  `commentID` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '	' ,
  `parentItemID` BIGINT(20) UNSIGNED NOT NULL COMMENT 'Comment belongs to parentID' ,
  `parentItemTypeID` BIGINT(20) UNSIGNED NOT NULL ,
  `commentActive` TINYINT(1) UNSIGNED NOT NULL ,
  `commentDatePublished` DATETIME NOT NULL ,
  `commentDateLastUpdated` DATETIME NOT NULL ,
  `userID` BIGINT(20) UNSIGNED NOT NULL ,
  PRIMARY KEY (`commentID`, `parentItemID`) ,
  UNIQUE INDEX `commentID_UNIQUE` (`commentID` ASC) ,
  INDEX `fk_comments_refParentItemTypes1` (`parentItemTypeID` ASC) ,
  INDEX `fk_comments_users1` (`userID` ASC) ,
  CONSTRAINT `fk_comments_refParentItemTypes1`
    FOREIGN KEY (`parentItemTypeID` )
    REFERENCES `awoms`.`refParentItemTypes` (`refParentItemTypeID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_comments_users1`
    FOREIGN KEY (`userID` )
    REFERENCES `awoms`.`users` (`userID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `awoms`.`domains`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `awoms`.`domains` ;

CREATE  TABLE IF NOT EXISTS `awoms`.`domains` (
  `domainID` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `domainName` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL ,
  `domainActive` TINYINT(1) NULL DEFAULT NULL ,
  PRIMARY KEY (`domainID`, `domainName`) ,
  UNIQUE INDEX `domainID_UNIQUE` (`domainID` ASC) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `awoms`.`images`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `awoms`.`images` ;

CREATE  TABLE IF NOT EXISTS `awoms`.`images` (
  `imageID` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `imageName` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL DEFAULT NULL ,
  `imageType` VARCHAR(15) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL DEFAULT NULL ,
  `imageSize` INT(11) NULL DEFAULT NULL ,
  PRIMARY KEY (`imageID`) ,
  UNIQUE INDEX `imageID_UNIQUE` (`imageID` ASC) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `awoms`.`keywords`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `awoms`.`keywords` ;

CREATE  TABLE IF NOT EXISTS `awoms`.`keywords` (
  `keywordID` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `keyword` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL ,
  PRIMARY KEY (`keywordID`, `keyword`) ,
  UNIQUE INDEX `keywordID_UNIQUE` (`keywordID` ASC) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `awoms`.`refBrandsDomains`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `awoms`.`refBrandsDomains` ;

CREATE  TABLE IF NOT EXISTS `awoms`.`refBrandsDomains` (
  `brandID` BIGINT(20) UNSIGNED NOT NULL ,
  `domainID` BIGINT(20) UNSIGNED NOT NULL ,
  `parentDomainID` BIGINT(20) UNSIGNED NOT NULL ,
  PRIMARY KEY (`brandID`, `domainID`) ,
  INDEX `fk_refBrandsDomains_Domains1` (`domainID` ASC) ,
  INDEX `fk_refBrandsDomains_refBrandsDomains1` (`parentDomainID` ASC) ,
  CONSTRAINT `fk_refBrandsDomains_Brands1`
    FOREIGN KEY (`brandID` )
    REFERENCES `awoms`.`brands` (`brandID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_refBrandsDomains_Domains1`
    FOREIGN KEY (`domainID` )
    REFERENCES `awoms`.`domains` (`domainID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_refBrandsDomains_refBrandsDomains1`
    FOREIGN KEY (`parentDomainID` )
    REFERENCES `awoms`.`refBrandsDomains` (`domainID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `awoms`.`refCategories`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `awoms`.`refCategories` ;

CREATE  TABLE IF NOT EXISTS `awoms`.`refCategories` (
  `parentItemID` BIGINT(20) UNSIGNED NOT NULL ,
  `parentItemTypeID` BIGINT(20) UNSIGNED NOT NULL ,
  `categoryID` BIGINT(20) UNSIGNED NOT NULL ,
  PRIMARY KEY (`parentItemID`) ,
  INDEX `fk_table1_Categories1` (`categoryID` ASC) ,
  INDEX `fk_refCategories_refParentItemTypes1` (`parentItemTypeID` ASC) ,
  CONSTRAINT `fk_refCategories_refParentItemTypes1`
    FOREIGN KEY (`parentItemTypeID` )
    REFERENCES `awoms`.`refParentItemTypes` (`refParentItemTypeID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_table1_Categories1`
    FOREIGN KEY (`categoryID` )
    REFERENCES `awoms`.`categories` (`categoryID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `awoms`.`refImages`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `awoms`.`refImages` ;

CREATE  TABLE IF NOT EXISTS `awoms`.`refImages` (
  `parentItemID` BIGINT(20) UNSIGNED NOT NULL ,
  `parentItemTypeID` BIGINT(20) UNSIGNED NOT NULL ,
  `imageID` BIGINT(20) UNSIGNED NOT NULL ,
  PRIMARY KEY (`parentItemID`, `imageID`) ,
  INDEX `fk_refArticlesImages_Images1` (`imageID` ASC) ,
  INDEX `fk_refArticlesImages_refParentItemTypes1` (`parentItemTypeID` ASC) ,
  CONSTRAINT `fk_refArticlesImages_Images1`
    FOREIGN KEY (`imageID` )
    REFERENCES `awoms`.`images` (`imageID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_refArticlesImages_refParentItemTypes1`
    FOREIGN KEY (`parentItemTypeID` )
    REFERENCES `awoms`.`refParentItemTypes` (`refParentItemTypeID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `awoms`.`refKeywords`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `awoms`.`refKeywords` ;

CREATE  TABLE IF NOT EXISTS `awoms`.`refKeywords` (
  `parentItemID` BIGINT(20) UNSIGNED NOT NULL ,
  `parentItemTypeID` BIGINT(20) UNSIGNED NOT NULL ,
  `keywordID` BIGINT(20) UNSIGNED NOT NULL ,
  `keywordCount` INT(11) NULL DEFAULT NULL ,
  PRIMARY KEY (`parentItemID`) ,
  INDEX `fk_refParentItemKeywords_keywords1` (`keywordID` ASC) ,
  INDEX `fk_refParentItemKeywords_refParentItemTypes1` (`parentItemTypeID` ASC) ,
  CONSTRAINT `fk_refParentItemKeywords_keywords1`
    FOREIGN KEY (`keywordID` )
    REFERENCES `awoms`.`keywords` (`keywordID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_refParentItemKeywords_refParentItemTypes1`
    FOREIGN KEY (`parentItemTypeID` )
    REFERENCES `awoms`.`refParentItemTypes` (`refParentItemTypeID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;



SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
