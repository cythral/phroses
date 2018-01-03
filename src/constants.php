<?php
namespace Phroses;

use \reqc;

define("Phroses", true);
define("Phroses\VERSION", "v0.5.20");
define("Phroses\SRC", __DIR__);
define("Phroses\SCHEMAVER", 2);
define("Phroses\DEPS", $deps);
define("Phroses\ROOT", (INPHAR) ? str_replace("phar://", "", dirname(SRC)) : dirname(SRC));
define("Phroses\INCLUDES", [
	"THEMES" => ROOT."/themes",
	"MODELS" => SRC."/models/classes",
	"VIEWS" => SRC."/views",
	"TPL" => SRC."/templates",
    "PLUGINS" => ROOT."/plugins",
	"META" => [ // ORDER OF THESE IS IMPORTANT
		"TRAITS" => SRC."/models/traits",
		"INTERFACES" => SRC."/models/interfaces"
	]
]);

define("Phroses\IMPORTANT_FILES", [
    "phroses.phar",
    ".htaccess",
    "README.md",
    "LICENSE",
    "phroses.conf",
    "themes",
    "plugins"
]);