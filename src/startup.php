<?php // This is the entry point for phroses

// check for minimum php version 7.0.0
if(version_compare(phpversion(), "7.0.0", "<")) {
  http_response_code(500);
  header("content-type: text/plain");
  die("A minimum of PHP v7.0.0 is required to run Phroses.");
}

foreach(["json", "pdo_mysql", "session", "date"] as $ext) {
  if(!extension_loaded($ext)) die("Please install or enable the $ext php extension to run phroses.");
}

// if no configuration file found, run installer
if(!file_exists(dirname(__DIR__)."/phroses.conf")) {
	include "install.php";
	return;
}

include "phroses.php"; // start phroses