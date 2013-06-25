USE `awoms`;

INSERT INTO `awoms`.`brands`
(`brandID`,
`brandName`,
`brandActive`)
VALUES
(
1,
'AWOMS',
1
);

-- Usergroups
INSERT INTO `awoms`.`usergroups`
(`usergroupID`,
`brandID`,
`usergroupName`,
`usergroupActive`,
`parentUserGroupID`)
VALUES
(
1,
1,
'Anonymous',
1,
NULL
);

-- Users
INSERT INTO `users`
(`userID`, `usergroupID`, `userActive`, `username`, `passphrase`)
VALUES
(1, 1, 1, 'anonymous', ''); -- Anonymous user

-- Parent Item Types
INSERT INTO `refParentItemTypes`
(`refParentItemTypeID`, `parentTypeLabel`)
VALUES
(1, 'Article'),
(2, 'Comment'),
(3, 'User');
