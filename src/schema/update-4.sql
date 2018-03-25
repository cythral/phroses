ALTER TABLE `pages` ADD `css` LONGTEXT NULL;
ALTER TABLE `sites` ADD `adminIP` varchar(200) NOT NULL AFTER `adminURI`;
UPDATE `options` SET `value`='4' WHERE `key`='schemaver';

ALTER TABLE `sites` MODIFY COLUMN `url` VARCHAR(255);
ALTER TABLE `sessions` MODIFY COLUMN `id` VARCHAR(255);
ALTER TABLE `pages` MODIFY COLUMN `uri` VARCHAR(255);

ALTER TABLE `pages` ADD CONSTRAINT `pages_siteID_fk` 
    FOREIGN KEY (`siteID`) REFERENCES `sites` (`id`)
    ON DELETE CASCADE;

/** CONVERT CHARSETS */
ALTER TABLE `options` CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE `sessions` CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE `pages` CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE `sites` CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;

/** CONVERT ENGINES **/
ALTER TABLE `options` ENGINE=InnoDB;
ALTER TABLE `sessions` ENGINE=InnoDB;
ALTER TABLE `pages` ENGINE=InnoDB;
ALTER TABLE `sites` ENGINE=InnoDB;

CREATE DEFINER = 'root'@'%' PROCEDURE `viewPage` (IN `url` VARCHAR(255) CHARSET utf8, IN `path` VARCHAR(255) CHARSET utf8) NOT DETERMINISTIC READS SQL DATA SQL SECURITY INVOKER
BEGIN 

SELECT 
  `sites`.*,
  `page`.*, 
  (`page`.`views` + 1) AS `views`,
  `sites`.`id` AS `siteID`,
  (@pid:=`page`.`id`) AS `id`
  FROM `sites` LEFT JOIN `pages` AS `page` ON `page`.`siteID`=`sites`.`id` AND `page`.`uri`=path  WHERE `sites`.`url`=url;

UPDATE `pages` SET `views` = `views` + 1 WHERE `id`=@pid;

END;