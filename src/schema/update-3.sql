ALTER TABLE `sites` ADD ( 
    `adminURI` varchar(800) DEFAULT '/admin' NOT NULL,
    `maintenance` BOOLEAN DEFAULT 0 NOT NULL
);

UPDATE `options` SET `value`='3' WHERE `key`='schemaver';