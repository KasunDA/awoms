USE gpfc;

DELETE FROM users
WHERE userID > 1;

DELETE FROM usergroups
WHERE brandID > 1;

DELETE FROM domains
WHERE domainID > 1;

DELETE FROM bodycontents
WHERE bodyContentID > 1;

DELETE FROM pages
WHERE pageID > 1;

DELETE FROM articles
WHERE articleID > 1;

DELETE FROM brands
WHERE brandID > 1;