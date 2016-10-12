<?php
namespace Phroses;

$uri = strtok($_SERVER["REQUEST_URI"], "?");
$path = (strpos($uri, ".")) ? strstr($uri, ".", true) : $uri;
$parts = explode("/", $path);
$directory = implode("/", array_slice($parts, 0, -1));
$filename = array_reverse($parts)[0];
$extension = (ltrim(strstr($uri, "."), "."));
$extension = ($extension == "") ? null : $extension;
$domainParts = explode(".", $_SERVER["HTTP_HOST"]);
parse_str(strtok("?"), $_GET);

define("Phroses\REQ", [
	"PROTOCOL" => $_SERVER["SERVER_PROTOCOL"],
	"H2PUSH" => (bool)($_SERVER["H2PUSH"] ?? false),
	"SSL" => (bool)($_SERVER["HTTPS"] ?? false),
	"HOST" => "{$domainParts[1]}.{$domainParts[0]}",
	"METHOD" => $_SERVER['REQUEST_METHOD'],
	"BASEURL" => $_SERVER["SERVER_NAME"],
	"FULLURL" => (((bool)($_SERVER["HTTPS"] ?? false)) ? "https://" : "http://").$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"],
	"PORT" => (int)$_SERVER['SERVER_PORT'],
	"IP" => $_SERVER["REMOTE_ADDR"],
	"USERAGENT" => $_SERVER["HTTP_USER_AGENT"],
	"VARS" => $_REQUEST,
	"ACCEPT" => explode(",", $_SERVER["HTTP_ACCEPT"]),
	"DIRECTORY" => $directory,
	"FILENAME" => $filename,
	"EXTENSION" => $extension,
	"FILE" => $filename.((isset($extension)) ? ".".$extension : ""),
	"PATH" => $directory."/".$filename.((isset($extension)) ? ".".$extension : ""),
	"SUBDOMAIN" => (count($domainParts) == 2) ? "main" : implode(".", array_slice($domainParts, 2)),
	"TYPE" => (isset($extension)) ? "asset" : "page"
]);