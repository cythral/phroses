<?php

namespace Phroses\Testing;

include __DIR__."/../src/constants.php";

// setup autoloader, functions
$loader = include \Phroses\ROOT."/vendor/autoload.php";
$loader->addPsr4("Phroses\\", \Phroses\SRC."/models");
include \Phroses\SRC."/functions.php";

use \PDO;
use \PDOException;
use \inix\Config as inix;

define("Phroses\SITE", [
    "ID" => null,
    "NAME" => null,
    "THEME" => null,
    "ADMINURI" => null,
    "USERNAME" => null,
    "PASSWORD" => null,
    "PAGE" => null,
    "MAINTENANCE" => null
]);

inix::load(\Phroses\ROOT."/phroses.conf");
$conf = inix::get("database");

try {
    $pdo = new PDO("mysql:host=".$conf["host"].";dbname=".$conf["name"], $conf["user"], $conf["password"]);
} catch(PDOException $e) {
    echo "Database Connection failed.";
    exit(1);
}

// backup any existing data
exec("mysqldump --user={$conf["user"]} --password={$conf["password"]} --host={$conf["host"]} {$conf["name"]} > ".\Phroses\ROOT."/tests/backup.sql");

// reinstall schema
$pdo->query(file_get_contents(\Phroses\SRC."/schema/install.sql"));

// get dataset
inix::set("test.password", bin2hex(openssl_random_pseudo_bytes(12)));
$dataset = file_get_contents(\Phroses\ROOT."/tests/dataset.json");
$dataset = str_replace("{password}", password_hash(inix::get("pepper").inix::get("test.password"), PASSWORD_DEFAULT), $dataset);
$dataset = json_decode($dataset, true);

// insert data
foreach($dataset as $tablename => $table) {

    foreach($table as $row) {
        $q = "INSERT INTO `{$tablename}` (";
        foreach($row as $key => $val) $q .= "`{$key}`, ";
        $q = rtrim($q, ", ").") VALUES (";
        foreach($row as $key => $val) $q .= ":{$key}, ";
        $q = rtrim($q, ", ").")";

        echo $q;
        $stmt = $pdo->prepare($q);
        foreach($row as $key => $val) $stmt->bindValue(":{$key}", $val);
        $stmt->execute();
        var_dump($stmt->errorInfo());
    }

}


class TestCase extends \PHPUnit\Framework\TestCase {
    public function assertArrayEquals($expected, $actual) {
        $this->assertEquals($expected, $actual, "\$canonicalize = true", $delta = 0.0, $maxDepth = 10, $canonicalize = true);
    }
}