<?php
namespace Phroses;

use \reqc;

define("Phroses", true);
define("Phroses\VERSION", "v0.5.15");
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

const MIME_TYPES = [
	"" => "text/html; charset=utf8",
	"php" => "text/html; charset=utf8",
	"html" => "text/html; charset=utf8",
	"xml" => "application/xml; charset=utf8",
	"json" => "application/json; charset=utf8",
	"js" => "text/javascript; charset=utf8",
	"css" => "text/css; charset=utf8",
	"woff" => "application/font-woff",
	"woff2" => "font/woff2",
	"ttf" => "font/ttf",
	"png" => "image/png",
	"jpg" => "image/jpeg",
	"jpeg" => "image/jpeg",
	"gif" => "image/gif",
	"pdf" => "application/pdf",
	"webp" => "image/webp",
	"otf" => "application/font-otf",
	"ico" => "image/x-icon",
	"tpl" => "text/html; charset=utf8"
];

if(reqc\TYPE != "cli") {
	if(array_key_exists(strtolower(reqc\EXTENSION), MIME_TYPES)) header("content-type: ".MIME_TYPES[strtolower(reqc\EXTENSION)]);
	else header("content-type: ".MIME_TYPES[""]);
}

