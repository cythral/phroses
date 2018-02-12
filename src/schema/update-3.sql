ALTER TABLE `sites` ADD ( 
    `adminURI` varchar(800) DEFAULT '/admin' NOT NULL 
);

UPDATE `options` SET `value`='3' WHERE `key`='schemaver';