<?php
/**
 * This file defines a number of constants that are used throughout the application.
 */

namespace Phroses;

define("Phroses", true);
define("Phroses\VERSION", "v0.8.0-dev");
define("Phroses\SRC", __DIR__); // location of the src folder
define("Phroses\SCHEMAVER", 4); // database schema version
define("Phroses\INPHAR", strpos(__DIR__, "phar://") !== false); // if in the packaged phar or not
define("Phroses\ROOT", (INPHAR) ? str_replace("phar://", "", dirname(SRC)) : dirname(SRC)); // where phroses is installed
define("Phroses\DEPS", [
	"PHP" => "7.2.0",
	"MYSQL" => "5.6.0",
	"EXTS" => [ "pdo_mysql", "json", "dom", "session", "date", "curl" ]
]);

define("Phroses\INCLUDES", [ // location of various files that are included
	"THEMES" => ROOT."/themes",
	"MODELS" => SRC."/models/classes",
	"VIEWS" => SRC."/views",
	"TPL" => SRC."/templates",
	"PLUGINS" => ROOT."/plugins",
	"UPLOADS" => ROOT."/uploads"
]);

define("Phroses\IMPORTANT_FILES", [ // important files to backup during upgrades
	"phroses.phar",
	".htaccess",
	"README.md",
	"LICENSE",
	"phroses.conf",
	"themes",
	"plugins"
]);