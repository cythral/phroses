ALTER TABLE `options` DROP COLUMN `id`;
ALTER TABLE `options` ADD PRIMARY KEY (`key`);
UPDATE `options` SET `value`='2' WHERE `key`='schemaver';

ALTER TABLE `pages` ADD (
    `public` BOOLEAN DEFAULT TRUE NOT NULL
);