<?php 
// WELCOME TO PHROSES
// this is the entry point
// lets check for versions
// and make sure we take care of even the most dumb users here
if(!function_exists("json_decode") || @__DIR__ == "__DIR__") die("ur php sux");

define("INPHAR", (strpos(__DIR__, "phar://") !== false) ? true : false);
$deps = array(
  "PHP" => "7.0.0",
  "MYSQL" => "5.0.0",
  "EXTS" => array( "pdo_mysql", "json", "session", "date" )
);


if(version_compare(phpversion(), $deps["PHP"], "<")) {
  http_response_code(500);
  header("content-type: text/plain");
  die("A minimum of PHP {$deps['PHP']} is required to run Phroses.");
}


foreach($deps["EXTS"] as $ext) {
  if(!extension_loaded($ext)) {
    http_response_code(500);
    header("content-type: text/plain");
    die("Please install or enable the {$ext} php extension to run phroses.");
  }
}

// ok now i can really start coding
include "phroses.php";