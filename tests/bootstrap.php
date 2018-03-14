<?php

namespace Phroses;

define("Phroses\TESTING", true);
include __DIR__."/../src/constants.php";

// setup autoloader, functions
$loader = include ROOT."/vendor/autoload.php";
$loader->addPsr4("Phroses\\", SRC."/models");
include SRC."/functions.php";

use inix\Config as inix;
inix::load(ROOT."/phroses.conf");
@inix::set("test-password.text", bin2hex(random_bytes(12)));
@inix::set("test-password.hash", password_hash(inix::get("pepper").inix::get("test-password.text"), PASSWORD_DEFAULT));

// include testcase
include INCLUDES["TESTS"]."/testcase.php";

$conf = inix::get("test-database") ?? inix::get("database");
$db = \Phroses\Database\Database::getInstance($conf["host"], $conf["name"], $conf["user"], $conf["password"]);

// setup mock phroses object
abstract class Phroses { static public $page; static public $site; }
Phroses::$page = new Page([ "id" => null, "type" => "page", "content" => null, "datecreated" => null, "datemodified" => null, "title" => null, "views" => 1, "public" => true ]);
Phroses::$site = new Site([ "id" => null, "name" => null, "theme" => 'bloom', "url" => null, "adminURI" => null, "adminUsername" => null, "adminPassword" => null, "maintenance" => false ]);
