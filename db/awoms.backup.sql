-- MySQL dump 10.13  Distrib 5.5.31, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: awoms
-- ------------------------------------------------------
-- Server version	5.5.31-0ubuntu0.12.10.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `articles`
--

DROP TABLE IF EXISTS `articles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `articles` (
  `articleID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `articleName` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `articleActive` tinyint(1) DEFAULT NULL,
  `articleShortDescription` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `articleLongDescription` text COLLATE utf8_unicode_ci,
  `articleDatePublished` datetime DEFAULT NULL,
  `articleDateLastReviewed` datetime DEFAULT NULL,
  `articleDateLastUpdated` datetime DEFAULT NULL,
  `articleDateExpires` datetime DEFAULT NULL,
  `userID` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`articleID`),
  UNIQUE KEY `articleID_UNIQUE` (`articleID`),
  KEY `fk_articles_users1` (`userID`),
  CONSTRAINT `fk_articles_users1` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `articles`
--

LOCK TABLES `articles` WRITE;
/*!40000 ALTER TABLE `articles` DISABLE KEYS */;
INSERT INTO `articles` VALUES (1,'test1',1,'','',NULL,NULL,NULL,NULL,1),(2,'inac',0,'','',NULL,NULL,NULL,NULL,1),(3,'yea',1,'asdf','ceasdf',NULL,NULL,NULL,NULL,1),(4,'iminactive',0,'','',NULL,NULL,NULL,NULL,1),(5,'etest1',1,'asdf','fdfadfafsdfdsa',NULL,NULL,NULL,NULL,1),(6,'This is a real article TEST!',1,'See shorty','CHECK IT OUT its not that lONG',NULL,NULL,NULL,NULL,1),(7,'@todo: header links write/viewall',1,'','',NULL,NULL,NULL,NULL,1),(8,'@todo: articles home -> include viewall',1,'','',NULL,NULL,NULL,NULL,1),(9,'@todo: prev/next article in view article header',1,'','',NULL,NULL,NULL,NULL,1),(10,'@todo: html header seo title',1,'','',NULL,NULL,NULL,NULL,1),(11,'@todo: datetimes',1,'','',NULL,NULL,NULL,NULL,1);
/*!40000 ALTER TABLE `articles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bodyContents`
--

DROP TABLE IF EXISTS `bodyContents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bodyContents` (
  `bodyContentID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `parentItemID` bigint(20) unsigned NOT NULL,
  `parentItemTypeID` bigint(20) unsigned NOT NULL,
  `bodyContentActive` tinyint(1) unsigned NOT NULL,
  `bodyContentDateModified` datetime DEFAULT NULL,
  `bodyContentText` text COLLATE utf8_unicode_ci,
  `userID` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`bodyContentID`,`parentItemID`,`bodyContentActive`),
  UNIQUE KEY `commentBodyID_UNIQUE` (`bodyContentID`),
  KEY `fk_bodyContents_refParentItemTypes1` (`parentItemTypeID`),
  KEY `fk_bodyContents_users1` (`userID`),
  CONSTRAINT `fk_bodyContents_refParentItemTypes1` FOREIGN KEY (`parentItemTypeID`) REFERENCES `refParentItemTypes` (`refParentItemTypeID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_bodyContents_users1` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bodyContents`
--

LOCK TABLES `bodyContents` WRITE;
/*!40000 ALTER TABLE `bodyContents` DISABLE KEYS */;
INSERT INTO `bodyContents` VALUES (1,1,1,0,'2012-01-03 04:06:08','a',1),(2,1,1,0,'2012-01-03 04:06:08','alasl[\'',1),(3,1,1,1,'2012-01-03 04:06:08','alpha',1),(4,2,1,1,'2012-01-03 04:06:08','tes',1),(5,3,1,1,'2012-01-03 04:06:08','asdffasdfsafdsfdsfdsfsfds',1),(6,4,1,0,'2012-01-03 04:06:08','test',1),(7,4,1,0,'2012-01-03 04:06:08','test',1),(8,4,1,0,'2012-01-03 04:06:08','test',1),(9,4,1,1,'2012-01-03 04:06:08','test',1),(10,5,1,0,'2012-01-03 04:06:08','fdfafsdfline1\r\nline2\r\n\r\nlin44444!!',1),(11,5,1,1,'2012-01-03 04:06:08','fdfafsdfline1\r\nline2\r\n\r\nlin44444!!',1),(12,6,1,1,'2012-01-03 04:06:08','Hello,\r\n\r\nIm an article,\r\n\r\n-Goodbye,',1),(13,7,1,1,'2012-01-03 04:06:08','',1),(14,8,1,1,'2012-01-03 04:06:08','',1),(15,9,1,1,'2012-01-03 04:06:08','',1),(16,10,1,1,'2012-01-03 04:06:08','',1),(17,11,1,1,'2012-01-03 04:06:08','',1);
/*!40000 ALTER TABLE `bodyContents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `brands`
--

DROP TABLE IF EXISTS `brands`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `brands` (
  `brandID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `brandName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `brandActive` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`brandID`,`brandName`),
  UNIQUE KEY `brandID_UNIQUE` (`brandID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `brands`
--

LOCK TABLES `brands` WRITE;
/*!40000 ALTER TABLE `brands` DISABLE KEYS */;
/*!40000 ALTER TABLE `brands` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `categoryID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `categoryName` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `categoryActive` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`categoryID`),
  UNIQUE KEY `categoryID_UNIQUE` (`categoryID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comments` (
  `commentID` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '	',
  `parentItemID` bigint(20) unsigned NOT NULL COMMENT 'Comment belongs to parentID',
  `parentItemTypeID` bigint(20) unsigned NOT NULL,
  `userID` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`commentID`,`parentItemID`),
  UNIQUE KEY `commentID_UNIQUE` (`commentID`),
  KEY `fk_comments_refParentItemTypes1` (`parentItemTypeID`),
  KEY `fk_comments_users1` (`userID`),
  CONSTRAINT `fk_comments_refParentItemTypes1` FOREIGN KEY (`parentItemTypeID`) REFERENCES `refParentItemTypes` (`refParentItemTypeID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_comments_users1` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comments`
--

LOCK TABLES `comments` WRITE;
/*!40000 ALTER TABLE `comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `domains`
--

DROP TABLE IF EXISTS `domains`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `domains` (
  `domainID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `domainName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `domainActive` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`domainID`,`domainName`),
  UNIQUE KEY `domainID_UNIQUE` (`domainID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `domains`
--

LOCK TABLES `domains` WRITE;
/*!40000 ALTER TABLE `domains` DISABLE KEYS */;
/*!40000 ALTER TABLE `domains` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `images`
--

DROP TABLE IF EXISTS `images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `images` (
  `imageID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `imageName` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `imageType` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `imageSize` int(11) DEFAULT NULL,
  PRIMARY KEY (`imageID`),
  UNIQUE KEY `imageID_UNIQUE` (`imageID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `images`
--

LOCK TABLES `images` WRITE;
/*!40000 ALTER TABLE `images` DISABLE KEYS */;
/*!40000 ALTER TABLE `images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `keywords`
--

DROP TABLE IF EXISTS `keywords`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `keywords` (
  `keywordID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `keyword` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`keywordID`,`keyword`),
  UNIQUE KEY `keywordID_UNIQUE` (`keywordID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `keywords`
--

LOCK TABLES `keywords` WRITE;
/*!40000 ALTER TABLE `keywords` DISABLE KEYS */;
/*!40000 ALTER TABLE `keywords` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `refBrandsDomains`
--

DROP TABLE IF EXISTS `refBrandsDomains`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `refBrandsDomains` (
  `brandID` bigint(20) unsigned NOT NULL,
  `domainID` bigint(20) unsigned NOT NULL,
  `parentDomainID` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`brandID`,`domainID`),
  KEY `fk_refBrandsDomains_Domains1` (`domainID`),
  KEY `fk_refBrandsDomains_refBrandsDomains1` (`parentDomainID`),
  CONSTRAINT `fk_refBrandsDomains_Brands1` FOREIGN KEY (`brandID`) REFERENCES `brands` (`brandID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_refBrandsDomains_Domains1` FOREIGN KEY (`domainID`) REFERENCES `domains` (`domainID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_refBrandsDomains_refBrandsDomains1` FOREIGN KEY (`parentDomainID`) REFERENCES `refBrandsDomains` (`domainID`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `refBrandsDomains`
--

LOCK TABLES `refBrandsDomains` WRITE;
/*!40000 ALTER TABLE `refBrandsDomains` DISABLE KEYS */;
/*!40000 ALTER TABLE `refBrandsDomains` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `refCategories`
--

DROP TABLE IF EXISTS `refCategories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `refCategories` (
  `parentItemID` bigint(20) unsigned NOT NULL,
  `parentItemTypeID` bigint(20) unsigned NOT NULL,
  `categoryID` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`parentItemID`),
  KEY `fk_table1_Categories1` (`categoryID`),
  KEY `fk_refCategories_refParentItemTypes1` (`parentItemTypeID`),
  CONSTRAINT `fk_refCategories_refParentItemTypes1` FOREIGN KEY (`parentItemTypeID`) REFERENCES `refParentItemTypes` (`refParentItemTypeID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_table1_Categories1` FOREIGN KEY (`categoryID`) REFERENCES `categories` (`categoryID`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `refCategories`
--

LOCK TABLES `refCategories` WRITE;
/*!40000 ALTER TABLE `refCategories` DISABLE KEYS */;
/*!40000 ALTER TABLE `refCategories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `refImages`
--

DROP TABLE IF EXISTS `refImages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `refImages` (
  `parentItemID` bigint(20) unsigned NOT NULL,
  `parentItemTypeID` bigint(20) unsigned NOT NULL,
  `imageID` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`parentItemID`,`imageID`),
  KEY `fk_refArticlesImages_Images1` (`imageID`),
  KEY `fk_refArticlesImages_refParentItemTypes1` (`parentItemTypeID`),
  CONSTRAINT `fk_refArticlesImages_Images1` FOREIGN KEY (`imageID`) REFERENCES `images` (`imageID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_refArticlesImages_refParentItemTypes1` FOREIGN KEY (`parentItemTypeID`) REFERENCES `refParentItemTypes` (`refParentItemTypeID`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `refImages`
--

LOCK TABLES `refImages` WRITE;
/*!40000 ALTER TABLE `refImages` DISABLE KEYS */;
/*!40000 ALTER TABLE `refImages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `refKeywords`
--

DROP TABLE IF EXISTS `refKeywords`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `refKeywords` (
  `parentItemID` bigint(20) unsigned NOT NULL,
  `parentItemTypeID` bigint(20) unsigned NOT NULL,
  `keywordID` bigint(20) unsigned NOT NULL,
  `keywordCount` int(11) DEFAULT NULL,
  PRIMARY KEY (`parentItemID`),
  KEY `fk_refParentItemKeywords_keywords1` (`keywordID`),
  KEY `fk_refParentItemKeywords_refParentItemTypes1` (`parentItemTypeID`),
  CONSTRAINT `fk_refParentItemKeywords_keywords1` FOREIGN KEY (`keywordID`) REFERENCES `keywords` (`keywordID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_refParentItemKeywords_refParentItemTypes1` FOREIGN KEY (`parentItemTypeID`) REFERENCES `refParentItemTypes` (`refParentItemTypeID`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `refKeywords`
--

LOCK TABLES `refKeywords` WRITE;
/*!40000 ALTER TABLE `refKeywords` DISABLE KEYS */;
/*!40000 ALTER TABLE `refKeywords` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `refParentItemTypes`
--

DROP TABLE IF EXISTS `refParentItemTypes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `refParentItemTypes` (
  `refParentItemTypeID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `parentTypeLabel` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`refParentItemTypeID`),
  UNIQUE KEY `refParentItemTypeID_UNIQUE` (`refParentItemTypeID`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `refParentItemTypes`
--

LOCK TABLES `refParentItemTypes` WRITE;
/*!40000 ALTER TABLE `refParentItemTypes` DISABLE KEYS */;
INSERT INTO `refParentItemTypes` VALUES (1,'Article'),(2,'Comment'),(3,'User');
/*!40000 ALTER TABLE `refParentItemTypes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `userID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `userActive` tinyint(1) DEFAULT NULL,
  `username` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `passphrase` char(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`userID`),
  UNIQUE KEY `userID_UNIQUE` (`userID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,1,'anonymous','');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2013-06-10 21:56:21
