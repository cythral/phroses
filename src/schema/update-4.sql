ALTER TABLE `pages` ADD `css` LONGTEXT NULL;
ALTER TABLE `sites` ADD `adminIP` varchar(200) NOT NULL AFTER `adminURI`;
UPDATE `options` SET `value`='4' WHERE `key`='schemaver';