-- v00.00.01 upgrade script

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

UPDATE awoms.comments
SET commentDateLastUpdated = '1997-01-03 04:05:01'
WHERE commentDateLastUpdated = '0000-00-00 00:00:00'
LIMIT 10000;

UPDATE awoms.articles
SET articleDateLastReviewed = '1997-01-03 04:05:01',
articleDateLastUpdated = '1997-01-03 04:05:01'
WHERE articleDateLastUpdated = '0000-00-00 00:00:00'
LIMIT 10000;

ALTER TABLE `awoms`.`articles` 
DROP PRIMARY KEY 
, ADD PRIMARY KEY (`articleID`, `articleActive`) ;

ALTER TABLE `awoms`.`brands` CHANGE COLUMN `brandActive` `brandActive` TINYINT(1) UNSIGNED NOT NULL  
, DROP PRIMARY KEY 
, ADD PRIMARY KEY (`brandID`, `brandName`, `brandActive`) ;

ALTER TABLE `awoms`.`categories` CHANGE COLUMN `categoryActive` `categoryActive` TINYINT(1) UNSIGNED NOT NULL  
, DROP PRIMARY KEY 
, ADD PRIMARY KEY (`categoryID`, `categoryActive`) ;

ALTER TABLE `awoms`.`comments` 
DROP PRIMARY KEY 
, ADD PRIMARY KEY (`commentID`, `parentItemID`, `commentActive`) ;

ALTER TABLE `awoms`.`domains` CHANGE COLUMN `domainActive` `domainActive` TINYINT(1) UNSIGNED NOT NULL
, DROP PRIMARY KEY 
, ADD PRIMARY KEY (`domainID`, `domainName`, `domainActive`) ;

ALTER TABLE `awoms`.`users` ADD COLUMN `usergroupID` BIGINT(19) UNSIGNED NOT NULL  AFTER `userID` , ADD COLUMN `userEmail` VARCHAR(255) NOT NULL  AFTER `userActive` , CHANGE COLUMN `username` `username` VARCHAR(45) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL AFTER `usergroupID` , CHANGE COLUMN `userActive` `userActive` TINYINT(1) NOT NULL  , 
  ADD CONSTRAINT `fk_users_usergroups1`
  FOREIGN KEY (`usergroupID` )
  REFERENCES `awoms`.`usergroups` (`usergroupID` )
  ON DELETE NO ACTION
  ON UPDATE NO ACTION
, DROP PRIMARY KEY 
, ADD PRIMARY KEY (`userID`, `usergroupID`, `username`, `userActive`, `userEmail`) 
, ADD INDEX `fk_users_usergroups1` (`usergroupID` ASC) ;

CREATE  TABLE IF NOT EXISTS `awoms`.`usergroups` (
  `usergroupID` BIGINT(19) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `brandID` BIGINT(20) UNSIGNED NOT NULL ,
  `usergroupName` VARCHAR(255) NULL DEFAULT NULL ,
  `usergroupActive` TINYINT(1) NULL DEFAULT NULL ,
  `parentUserGroupID` BIGINT(19) UNSIGNED NULL DEFAULT NULL ,
  PRIMARY KEY (`usergroupID`) ,
  UNIQUE INDEX `usergroupID_UNIQUE` (`usergroupID` ASC) ,
  INDEX `fk_usergroups_brands1` (`brandID` ASC) ,
  INDEX `fk_usergroups_usergroups1` (`parentUserGroupID` ASC) ,
  CONSTRAINT `fk_usergroups_brands1`
    FOREIGN KEY (`brandID` )
    REFERENCES `awoms`.`brands` (`brandID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_usergroups_usergroups1`
    FOREIGN KEY (`parentUserGroupID` )
    REFERENCES `awoms`.`usergroups` (`usergroupID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

CREATE  TABLE IF NOT EXISTS `awoms`.`acl` (
  `usergroupID` BIGINT(19) UNSIGNED NOT NULL ,
  `right` VARCHAR(255) NOT NULL ,
  `hasRight` TINYINT(1) UNSIGNED NULL DEFAULT NULL ,
  PRIMARY KEY (`usergroupID`) ,
  INDEX `fk_acl_rights1` (`right` ASC) ,
  CONSTRAINT `fk_acl_usergroups1`
    FOREIGN KEY (`usergroupID` )
    REFERENCES `awoms`.`usergroups` (`usergroupID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_acl_rights1`
    FOREIGN KEY (`right` )
    REFERENCES `awoms`.`rights` (`right` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

CREATE  TABLE IF NOT EXISTS `awoms`.`rights` (
  `right` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`right`) ,
  UNIQUE INDEX `rightID_UNIQUE` (`right` ASC) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
