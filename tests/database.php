<?php

use inix\Config as inix;

// get configuration
$conf = inix::get("test-database") ?? inix::get("database");

// connect
try {
    $pdo = new PDO("mysql:host={$conf['host']};dbname={$conf['name']};", $conf["user"], $conf["password"]);
} catch(PDOException $e) {
    echo "Could not setup database: {$e->getMessage()}\n";
    exit(1);
}

// setup schema
$pdo->query(file_get_contents(Phroses\SRC."/schema/install.sql"));

return $pdo;