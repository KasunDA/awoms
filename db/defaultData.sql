USE `GPFC`;

-- Brands
-- First brand is central umbrella brand
INSERT INTO `brands` 
(`brandID`, `brandName`, `brandActive`, `brandLabel`)
VALUES
(1, 'Goin\' Postal Franchise Corporation', 1, 'GPFC'),
(2, 'Goin\' Postal', 1, 'GP'),
(3, 'Hut no. 8', 1, 'Hut8');

-- Domains
INSERT INTO `domains`
(`domainID`, `brandID`, `domainName`, `domainActive`)
VALUES
(1, 1, 'dev.gpfc.com', 1),
(2, 2, 'dev.goinpostal.com', 1),
(3, 3, 'dev.hutno8.com', 1);

-- Usergroups
INSERT INTO `usergroups`
(`usergroupID`, `brandID`, `usergroupName`, `usergroupActive`,`parentUserGroupID`)
VALUES
(1, 1, 'Administrators', 1, NULL),
(2, 1, 'Store Owners', 1, NULL),
(3, 1, 'Users', 1, NULL);

-- Users
INSERT INTO `users`
(`userID`, `usergroupID`, `userActive`, `userName`, `passphrase`, `userEmail`)
VALUES
(1, 1, 1, 'GPAdmin', 'test', 'admin@goinpostal.com'),
(2, 1, 1, 'HutAdmin', 'test', 'admin@goinpostal.com');

-- Parent Item Types
INSERT INTO `refParentItemTypes`
(`refParentItemTypeID`, `parentTypeLabel`)
VALUES
(1, 'User'),
(2, 'Page'),
(3, 'Article'),
(4, 'Comment');

-- Default ACLs
INSERT INTO `aclDefaults`
(`brandID`, `usergroupID`, `userID`, `controller`, `create`, `read`, `update`, `delete`)
VALUES
(1, 1, NULL, 'brands', 1, 1, 1, 1),
(1, 1, NULL, 'domains', 1, 1, 1, 1),
(1, 1, NULL, 'usergroups', 1, 1, 1, 1),
(1, 1, NULL, 'users', 1, 1, 1, 1),
(1, 1, NULL, 'pages', 1, 1, 1, 1),
(1, 1, NULL, 'articles', 1, 1, 1, 1),
(1, 1, NULL, 'comments', 1, 1, 1, 1),

(1, 2, NULL, 'brands', 0, 0, 0, 0),
(1, 2, NULL, 'domains', 1, 1, 1, 1),
(1, 2, NULL, 'usergroups', 1, 1, 1, 1),
(1, 2, NULL, 'users', 1, 1, 1, 1),
(1, 2, NULL, 'pages', 1, 1, 1, 1),
(1, 2, NULL, 'articles', 1, 1, 1, 1),
(1, 2, NULL, 'comments', 1, 1, 1, 1),

(1, 3, NULL, 'brands', 0, 0, 0, 0),
(1, 3, NULL, 'domains', 0, 0, 0, 0),
(1, 3, NULL, 'usergroups', 0, 0, 0, 0),
(1, 3, NULL, 'users', 0, 0, 0, 0),
(1, 3, NULL, 'pages', 0, 0, 0, 0),
(1, 3, NULL, 'articles', 1, 1, 1, 1),
(1, 3, NULL, 'comments', 1, 1, 1, 1);
