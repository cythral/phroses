DROP TABLE IF EXISTS `options`;
DROP TABLE IF EXISTS `pages`;
DROP TABLE IF EXISTS `sites`;
DROP TABLE IF EXISTS `sessions`;


/******************************************
OPTIONS TABLE
*****************************************************/

CREATE TABLE `options` (
  `key` varchar(50) NOT NULL PRIMARY KEY,
  `value` text NOT NULL  
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/******************************************
SITES TABLE
*****************************************************/

CREATE TABLE `sites` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `url` varchar(255) NOT NULL UNIQUE,
  `theme` varchar(300) NOT NULL,
  `name` varchar(100) NOT NULL,
  `adminUsername` varchar(50) NOT NULL,
  `adminPassword` char(60) NOT NULL,
  `adminURI` varchar(800) DEFAULT '/admin' NOT NULL,
  `adminIP` varchar(200) DEFAULT '*' NOT NULL,
  `maintenance` BOOLEAN DEFAULT 0 NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

/******************************************
PAGES TABLE
*****************************************************/

CREATE TABLE `pages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `siteID` bigint(20) unsigned NOT NULL,
  `dateCreated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dateModified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `views` bigint(20) unsigned NOT NULL DEFAULT 0,
  `type` varchar(200) NOT NULL DEFAULT 'page',
  `title` varchar(2000) NOT NULL,
  `uri` varchar(255) NOT NULL,
  `content` LONGTEXT NOT NULL,
  `public` BOOLEAN DEFAULT TRUE NOT NULL,
  `css` LONGTEXT NULL,

  CONSTRAINT `pagesite_un` UNIQUE (`siteID`, `uri`),  
  CONSTRAINT `pages_siteID_fk` 
    FOREIGN KEY (`siteID`) REFERENCES `sites` (`id`)
    ON DELETE CASCADE
 
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

/******************************************
SESSION TABLE
*****************************************************/
CREATE TABLE `sessions` (
  `id` VARCHAR(255) NOT NULL PRIMARY KEY,
  `data` longtext NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/** SET DATABASE SCHEMA VERSION **/
INSERT INTO `options` (`key`, `value`) VALUES (
  'schemaver',
  '<{var::schemaver}>'
);

DELIMITER $$
CREATE DEFINER=`root`@`%` PROCEDURE `viewPage`(IN `url` VARCHAR(255) CHARSET utf8, IN `path` VARCHAR(255) CHARSET utf8)
    SQL SECURITY INVOKER
BEGIN 

SELECT 
	`page`.*, 
	`sites`.*, 
    (@pid:=`page`.`id`) AS `pageID`, 
    (`page`.`views` + 1) AS `views`
   	FROM `sites` LEFT JOIN `pages` AS `page` ON `page`.`siteID`=`sites`.`id` AND `page`.`uri`=path  WHERE `sites`.`url`=url;

UPDATE `pages` SET `views` = `views` + 1 WHERE `id`=@pid;

END$$
DELIMITER ;