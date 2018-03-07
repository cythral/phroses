<?php

include __DIR__."/../src/constants.php";

$loader = include \Phroses\ROOT."/vendor/autoload.php";
$loader->addPsr4("Phroses\\", \Phroses\SRC."/models");

use \inix\Config as inix;

// initiate database connection
inix::load(\Phroses\ROOT."/phroses.conf");
$conf = inix::get("database");
$pdo = new PDO("mysql:host=".$conf["host"].";dbname=".$conf["name"], $conf["user"], $conf["password"]);

// restore backup
$pdo->query("SELECT concat('DROP TABLE IF EXISTS ', table_name, ';') FROM information_schema.tables WHERE table_schema = '{$conf["name"]}';");
$pdo->query(file_get_contents(\Phroses\ROOT."/tests/backup.sql"));
unlink(\Phroses\ROOT."/tests/backup.sql");