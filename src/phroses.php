<?php

namespace Phroses;

// Define constants
define("SRC", __DIR__);
define("ROOT", dirname(SRC));
define("CONF", parse_ini_file(ROOT."/phroses.conf", true));
define("LOCATIONS", [
	"MODELS" => [ // ORDER OF THESE IS IMPORTANT
		"TRAITS" => SRC."/models/traits",
		"INTERFACES" => SRC."/models/interfaces",
		"CLASSES" => SRC."/models/classes",
	]
]);

abstract class Phroses {
	static public $conf;
	static private $ran = false;
	static private $modes = [
		"development" => [
			"display_errors" => 1,
			"error_reporting" => E_ALL
		],
		"production" => [
			"display_errors" => 0,
			"error_reporting" => 0
		]
	];
	
	static public function Start() {
		if(self::$ran) return true; // only run once
		include SRC."/functions.php"; // include functions
		
		self::SetupMode();
		self::LoadModels();
	}
	
	static public function SetupMode() {
		if(self::$ran) return; // only run once
		if(!array_key_exists(CONF["mode"], array_keys(self::$modes))) return false;
		foreach(self::$modes as $key => $val) ini_set($key, $val);
	}
	
	static public function LoadModels() {
		foreach(LOCATIONS["MODELS"] as $class => $loc) {
			foreach(FileList($loc) as $file) include $file;
		} 
	}
}

Phroses::start();