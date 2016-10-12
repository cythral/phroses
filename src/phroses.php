<?php

namespace Phroses;

// Define constants
define("Phroses\SRC", __DIR__);
define("Phroses\ROOT", dirname(SRC));
define("Phroses\CONF", parse_ini_file(ROOT."/phroses.conf", true));
define("Phroses\INCLUDES", [
	"THEMES" => ROOT."/themes",
	"MODELS" => SRC."/models/classes",
	"META" => [ // ORDER OF THESE IS IMPORTANT
		"TRAITS" => SRC."/models/traits",
		"INTERFACES" => SRC."/models/interfaces"
	]
]);

abstract class Phroses {
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
		include SRC."/request.php";
		
		self::SetupMode();
		self::LoadModels();
		self::LoadSiteInfo();
		self::RenderPage();
	}
	
	static public function SetupMode() {
		if(self::$ran) return; // only run once
		if(!array_key_exists(CONF["mode"], array_keys(self::$modes))) return false;
		foreach(self::$modes as $key => $val) ini_set($key, $val);
	}
	
	static public function LoadModels() {
		if(self::$ran) return; // only run once
		foreach(INCLUDES["META"] as $loc) {
			foreach(FileList($loc) as $file) include_once $file;
		}
		
		spl_autoload_register(function($class) {
			$class = strtolower(str_replace("Phroses\\", "", $class));
			if(file_exists(INCLUDES["MODELS"]."/{$class}.php"))
				include INCLUDES["MODELS"]."/{$class}.php";
		});
	}
	
	static public function LoadSiteInfo() {
		if(self::$ran) return;
		$info = DB::Query("SELECT `sites`.`id`, `sites`.`theme`, `pages`.`title`, `pages`.`content`, `pages`.`id` AS `pageID` FROM `sites` LEFT JOIN `pages` ON `pages`.`siteID`=`sites`.`id` AND `pages`.`uri`=? WHERE `sites`.`url`=?", [
			REQ["PATH"],
			REQ["BASEURL"]
		]);
		
		// Determine Response Type
		$response = "PAGE-200";
		if(count($info) == 0) $response = "SYSTEM-404"; 
		if(!isset(($info = $info[0])->pageID)) { // Page not found
			$response = "PAGE-404";
			if(REQ["TYPE"] == "asset" && file_exists(INCLUDES["THEMES"]."/{$info->theme}".REQ["PATH"])) $response = "ASSET-200";
		}
			
		// Setup the site constant
		define("Phroses\SITE", [
			"ID" => $info->id,
			"RESPONSE" => $response,
			"THEME" => $info->theme,
			"PAGE" => [
				"TITLE" => $info->title,
				"CONTENT" => $info->content
			]
		]);
	}
	
	static public function RenderPage() {
		$out = new Theme(SITE["THEME"]);
		$out->content = SITE["PAGE"]["CONTENT"];
		die($out);
	}
}

Phroses::start();