USE `GPFC`;

INSERT INTO `brands`
(`brandID`,
`brandName`,
`brandActive`,
`brandLabel`)
VALUES
(
1,
'Goin\' Postal Franchise Corporation',
1,
'GPFC'
);

-- Usergroups
INSERT INTO `usergroups`
(`usergroupID`,
`brandID`,
`usergroupName`,
`usergroupActive`,
`parentUserGroupID`)
VALUES
(
1,
1,
'Administrators',
1,
NULL
);

-- Users
INSERT INTO `users`
(`userID`, `usergroupID`, `userActive`, `userName`, `passphrase`, `userEmail`)
VALUES
(1, 1, 1, 'GPAdmin', 'test', 'admin@goinpostal.com');

-- Parent Item Types
INSERT INTO `refParentItemTypes`
(`refParentItemTypeID`, `parentTypeLabel`)
VALUES
(1, 'User'),
(2, 'Page'),
(3, 'Article'),
(4, 'Comment');

-- Domains
INSERT INTO `domains`
(`domainID`, `brandID`, `domainName`, `domainActive`)
VALUES
(1, 1, 'dev.gpfc.com', 1);
