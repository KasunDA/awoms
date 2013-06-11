USE `awoms`;

-- Users
INSERT INTO `users`
(`userID`, `userActive`, `username`, `passphrase`)
VALUES
(1, 1, 'anonymous', ''); -- Anonymous user

-- Parent Item Types
INSERT INTO `refParentItemTypes`
(`refParentItemTypeID`, `parentTypeLabel`)
VALUES
(1, 'Article'),
(2, 'Comment'),
(3, 'User');
