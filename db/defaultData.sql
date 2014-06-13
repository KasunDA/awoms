USE `GPFC`;

-- Parent Item Types
INSERT INTO `refParentItemTypes`
(`refParentItemTypeID`, `parentTypeLabel`)
VALUES
(1, 'User'),
(2, 'Page'),
(3, 'Article'),
(4, 'Comment');

-- Brands
-- First brand is central umbrella brand
INSERT INTO `brands` 
(`brandID`, `brandName`, `brandActive`, `brandLabel`)
VALUES
(1, 'Goin\' Postal Franchise Corporation', 1, 'GPFC')
-- ,
-- (2, 'Goin\' Postal', 1, 'GP'),
-- (3, 'Hut no. 8', 1, 'Hut8')
;

-- Domains
INSERT INTO `domains`
(`domainID`, `brandID`, `domainName`, `domainActive`)
VALUES
(1, 1, 'dev.gpfc.com', 1)
-- ,
-- (2, 2, 'dev.goinpostal.com', 1),
-- (3, 3, 'dev.hutno8.com', 1)
;

-- Usergroups
INSERT INTO `usergroups`
(`usergroupID`, `brandID`, `usergroupName`, `usergroupActive`,`parentUserGroupID`)
VALUES
(1, 1, 'Administrators', 1, NULL),(2, 1, 'Store Owners', 1, NULL),(3, 1, 'Users', 1, NULL)
-- ,
-- (4, 2, 'Administrators', 1, NULL),(5, 2, 'Store Owners', 1, NULL),(6, 2, 'Users', 1, NULL),
-- (7, 3, 'Administrators', 1, NULL),(8, 3, 'Store Owners', 1, NULL),(9, 3, 'Users', 1, NULL)
;

-- Users
INSERT INTO `users`
(`userID`, `usergroupID`, `userActive`, `userName`, `passphrase`, `userEmail`)
VALUES
(1, 1, 1, 'GPFCAdmin', 'test', 'admin@gpfc.com')
-- ,
-- (2, 4, 1, 'GPAdmin', 'test', 'admin@goinpostal.com'),
-- (3, 7, 1, 'Hut8Admin', 'test', 'admin@hutno8.com'),

-- (4, 5, 1, 'GPStoreOwner', 'test', 'storeowner@goinpostal.com'),
-- (5, 8, 1, 'Hut8StoreOwner', 'test', 'storeowner@hutno8.com'),
-- (6, 9, 1, 'Hut8User', 'test', 'user@hutno8.com')
;

-- Default ACLs
INSERT INTO `acls`
(`brandID`, `usergroupID`, `userID`, `controller`, `create`, `read`, `update`, `delete`)
VALUES
-- 1: Admin defaults
(NULL, 1, NULL, 'brands', 1, 1, 1, 1),
(NULL, 1, NULL, 'domains', 1, 1, 1, 1),
(NULL, 1, NULL, 'menus', 1, 1, 1, 1),
(NULL, 1, NULL, 'usergroups', 1, 1, 1, 1),
(NULL, 1, NULL, 'users', 1, 1, 1, 1),
(NULL, 1, NULL, 'pages', 1, 1, 1, 1),
(NULL, 1, NULL, 'articles', 1, 1, 1, 1),
(NULL, 1, NULL, 'comments', 1, 1, 1, 1),
-- 2: Store Owner defaults
(NULL, 2, NULL, 'brands', 0, 0, 0, 0),
(NULL, 2, NULL, 'domains', 1, 1, 1, 1),
(NULL, 2, NULL, 'menus', 0, 1, 1, 0),
(NULL, 2, NULL, 'usergroups', 1, 1, 1, 1),
(NULL, 2, NULL, 'users', 1, 1, 1, 1),
(NULL, 2, NULL, 'pages', 1, 1, 1, 1),
(NULL, 2, NULL, 'articles', 1, 1, 1, 1),
(NULL, 2, NULL, 'comments', 1, 1, 1, 1),
-- 3: User defaults
(NULL, 3, NULL, 'brands', 0, 0, 0, 0),
(NULL, 3, NULL, 'domains', 0, 0, 0, 0),
(NULL, 3, NULL, 'menus', 0, 0, 0, 0),
(NULL, 3, NULL, 'usergroups', 0, 0, 0, 0),
(NULL, 3, NULL, 'users', 0, 0, 0, 0),
(NULL, 3, NULL, 'pages', 0, 0, 0, 0),
(NULL, 3, NULL, 'articles', 1, 1, 1, 1),
(NULL, 3, NULL, 'comments', 1, 1, 1, 1);

-- Default Menus
INSERT INTO `menus`
(`menuID`,`brandID`,`menuName`,`menuActive`)
VALUES
(1, 1, 'Default Heading Navigation Menu', 1);

-- Default Menu Links
INSERT INTO `menulinks`
(`linkID`,`menuID`,`sortOrder`,`parentLinkID`,`display`,`url`,`linkActive`)
VALUES
(1, 1, 1, NULL, 'Home', '/', 1),
(2, 1, 2, NULL, 'About Us', '/about', 1),
(3, 1, 3, NULL, 'Contact Us', '/contact', 1),
(4, 1, 4, NULL, 'Franchise Login', '/owners', 1);