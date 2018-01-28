/******************************************
OPTIONS TABLE
*****************************************************/
DROP TABLE IF EXISTS `options`;
CREATE TABLE `options` (
  `key` varchar(50) NOT NULL PRIMARY KEY,
  `value` text NOT NULL  
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/******************************************
SITES TABLE
*****************************************************/
DROP TABLE IF EXISTS `sites`;
CREATE TABLE `sites` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `url` varchar(1000) NOT NULL UNIQUE,
  `theme` varchar(300) NOT NULL,
  `name` varchar(100) NOT NULL,
  `adminUsername` varchar(50) NOT NULL,
  `adminPassword` char(60) NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

/******************************************
PAGES TABLE
*****************************************************/
DROP TABLE IF EXISTS `pages`;
CREATE TABLE `pages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `siteID` bigint(20) unsigned NOT NULL,
  `dateCreated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dateModified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `views` bigint(20) unsigned NOT NULL DEFAULT 0,
  `type` varchar(200) NOT NULL DEFAULT 'page',
  `title` varchar(2000) NOT NULL,
  `uri` varchar(800) NOT NULL,
  `content` LONGTEXT NOT NULL,
  `public` BOOLEAN DEFAULT TRUE NOT NULL,

  CONSTRAINT `pagesite_un` UNIQUE (`siteID`, `uri`),  
  CONSTRAINT `pages_siteID_fk` 
    FOREIGN KEY (`siteID`) REFERENCES `sites` (`id`)
    ON DELETE CASCADE
 
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;


/******************************************
SESSION TABLE
*****************************************************/
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(600) NOT NULL PRIMARY KEY,
  `data` longtext NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/** SET DATABASE SCHEMA VERSION **/
INSERT INTO `options` (`key`, `value`) VALUES (
  'schemaver',
  '<{var::schemaver}>'
);
