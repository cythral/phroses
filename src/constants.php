<?php

namespace Phroses;

define("Phroses", true);
define("Phroses\VERSION", "v0.7.0");
define("Phroses\SRC", __DIR__);
define("Phroses\SCHEMAVER", 3);
define("Phroses\INPHAR", strpos(__DIR__, "phar://") !== false);
define("Phroses\ROOT", (INPHAR) ? str_replace("phar://", "", dirname(SRC)) : dirname(SRC));
define("Phroses\INCLUDES", [
	"THEMES" => ROOT."/themes",
	"MODELS" => SRC."/models/classes",
	"VIEWS" => SRC."/views",
	"TPL" => SRC."/templates",
    "PLUGINS" => ROOT."/plugins",
    "UPLOADS" => ROOT."/uploads"
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