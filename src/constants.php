<?php
namespace Phroses;

define("Phroses", true);
define("Phroses\VERSION", "v0.5.13");
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

if(php_sapi_name() != "cli" || isset($_ENV["PHR_TESTING"])) {
	$uri = strtok($_SERVER["REQUEST_URI"], "?");
	$path = (strpos($uri, ".")) ? strstr($uri, ".", true) : $uri;
	$parts = explode("/", $path);
	$directory = implode("/", array_slice($parts, 0, -1));
	$filename = array_reverse($parts)[0];
	$extension = (ltrim(strstr($uri, "."), "."));
	$extension = ($extension == "") ? null : $extension;
	$domainParts = array_reverse(explode(".", $_SERVER["HTTP_HOST"]));
	parse_str(strtok("?"), $_GET);
}

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

define("Phroses\REQ", (php_sapi_name() != "cli" || isset($_ENV["PHR_TESTING"])) ? [
	"PROTOCOL" => $_SERVER["SERVER_PROTOCOL"],
	"H2PUSH" => (bool)($_SERVER["H2PUSH"] ?? false),
	"SSL" => (bool)($_SERVER["HTTPS"] ?? false),
	"HOST" => strtok($_SERVER["HTTP_HOST"], ":"),
	"METHOD" => $_SERVER['REQUEST_METHOD'],
	"BASEURL" => $_SERVER["SERVER_NAME"],
	"FULLURL" => (((bool)($_SERVER["HTTPS"] ?? false)) ? "https://" : "http://").$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"],
	"URI" => strtok($_SERVER["REQUEST_URI"], "?"),
	"PORT" => (int)$_SERVER['SERVER_PORT'],
	"IP" => $_SERVER["REMOTE_ADDR"],
	"USERAGENT" => $_SERVER["HTTP_USER_AGENT"] ?? '',
	"VARS" => $_REQUEST,
	"ACCEPT" => explode(",", ($_SERVER["HTTP_ACCEPT"] ?? '')),
	"DIRECTORY" => $directory,
	"FILENAME" => $filename,
	"EXTENSION" => $extension,
	"FILE" => $filename.((isset($extension)) ? ".".$extension : ""),
	"PATH" => $directory."/".$filename.((isset($extension)) ? ".".$extension : ""),
	"SUBDOMAIN" => (count($domainParts) == 2) ? "main" : implode(".", array_slice($domainParts, 2)),
	"TYPE" => (isset($extension)) ? "asset" : "page"
] : ["TYPE" => "cli"]);

if(REQ["TYPE"] != "cli") {
	if(array_key_exists(strtolower(REQ["EXTENSION"]), MIME_TYPES)) header("content-type: ".MIME_TYPES[strtolower(REQ["EXTENSION"])]);
	else header("content-type: ".MIME_TYPES[""]);
    parse_str(file_get_contents('php://input'), $_REQUEST);
}

