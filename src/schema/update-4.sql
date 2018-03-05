ALTER TABLE `pages` ADD (
    `css` LONGTEXT NULL
);

UPDATE `options` SET `value`='4' WHERE `key`='schemaver';