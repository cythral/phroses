<?php

namespace Phroses;

include __DIR__."/../src/constants.php";

// setup autoloader, functions
$loader = include ROOT."/vendor/autoload.php";
$loader->addPsr4("Phroses\\", SRC."/models");
include SRC."/functions.php";

abstract class Phroses { static public $site; }
Phroses::$site = new Site([
    "id" => null,
    "url" => null,
    "name" => null,
    "theme" => null,
    "adminURI" => null,
    "adminUsername" => null,
    "adminPassword" => null,
    "maintenance" => false,
]);

Phroses::$site->useDB = false;

namespace Phroses\Testing;

use \PDO;
use \PDOException;
use \inix\Config as inix;

inix::load(\Phroses\ROOT."/phroses.conf");
$conf = inix::get("database");
exec("mysqldump --user={$conf["user"]} --password={$conf["password"]} --host={$conf["host"]} {$conf["name"]} > ".\Phroses\ROOT."/tests/backup.sql");
@inix::set("test.password", password_hash(inix::get("pepper").bin2hex(openssl_random_pseudo_bytes(12)), PASSWORD_DEFAULT));

class TestCase extends \PHPUnit\Framework\TestCase {
    public function assertArrayEquals($expected, $actual) {
        $this->assertEquals($expected, $actual, "\$canonicalize = true", $delta = 0.0, $maxDepth = 10, $canonicalize = true);
    }

    static public function resetDB() {
        inix::load(\Phroses\ROOT."/phroses.conf");
        $conf = inix::get("database");

        try {
            $pdo = new PDO("mysql:host=".$conf["host"].";dbname=".$conf["name"], $conf["user"], $conf["password"]);
        } catch(PDOException $e) {
            echo "Database Connection failed.";
            exit(1);
        }

        // reinstall schema
        $pdo->query(file_get_contents(\Phroses\SRC."/schema/install.sql"));

        // get dataset
        $dataset = file_get_contents(\Phroses\ROOT."/tests/dataset.json");
        $dataset = str_replace("{password}", inix::get("test.password"), $dataset);
        $dataset = json_decode($dataset, true);

        // insert data
        foreach($dataset as $tablename => $table) {

            foreach($table as $row) {
                $q = "INSERT INTO `{$tablename}` (";
                foreach($row as $key => $val) $q .= "`{$key}`, ";
                $q = rtrim($q, ", ").") VALUES (";
                foreach($row as $key => $val) $q .= ":{$key}, ";
                $q = rtrim($q, ", ").")";

                $stmt = $pdo->prepare($q);
                foreach($row as $key => $val) $stmt->bindValue(":{$key}", $val);
                $stmt->execute();
            }
        }

        unset($pdo);
    }
}

TestCase::resetDB();