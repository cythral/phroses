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
@inix::set("test-password", password_hash(inix::get("pepper").bin2hex(openssl_random_pseudo_bytes(12)), PASSWORD_DEFAULT));

// include testcase
include INCLUDES["TESTS"]."/testcase.php";

// setup mock phroses object
abstract class Phroses { static public $page; static public $site; }
Phroses::$page = new Page([ "id" => null, "type" => "page", "content" => null, "datecreated" => null, "datemodified" => null, "title" => null, "views" => 1, "public" => true ]);
Phroses::$site = new Site([ "id" => null, "name" => null, "theme" => 'bloom', "url" => null, "adminURI" => null, "adminUsername" => null, "adminPassword" => null, "maintenance" => false ]);
