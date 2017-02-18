<?php

namespace Phroses;

// Define constants
define("Phroses\SRC", __DIR__);
define("Phroses\ROOT", dirname(SRC));
define("Phroses\CONF", parse_ini_file(ROOT."/phroses.conf", true));
define("Phroses\INCLUDES", [
	"THEMES" => ROOT."/themes",
	"MODELS" => SRC."/models/classes",
	"VIEWS" => SRC."/views",
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
		if(self::$ran) return; // only run once
		include SRC."/functions.php"; // include functions
		include SRC."/request.php";
		
		self::SetupMode();
		self::LoadModels();
		self::LoadSiteInfo();
		Session::start();
		register_shutdown_function(function() {
			session_write_close();
		});
		self::Render();
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
				include_once INCLUDES["MODELS"]."/{$class}.php";
		});
	}
	
	static public function LoadSiteInfo() {
		if(self::$ran) return;
		$info = DB::Query("SELECT `sites`.`id`, `sites`.`theme`, `sites`.`name`, `sites`.`adminUsername`, `sites`.`adminPassword`, `pages`.`title`, `pages`.`content`, `pages`.`id` AS `pageID`, `pages`.`type` FROM `sites` LEFT JOIN `pages` ON `pages`.`siteID`=`sites`.`id` AND `pages`.`uri`=? WHERE `sites`.`url`=?", [
			REQ["PATH"],
			REQ["BASEURL"]
		]);
		
		// Determine Response Type
		$response = "PAGE-200";
		if(count($info) == 0) $response = "SYSTEM-404"; 
		if(!isset(($info = $info[0])->pageID)) $response = "UNDETERMINED";
		if(REQ["PATH"] != "/" && (file_exists(INCLUDES["VIEWS"].REQ["PATH"].".php") || 
		   file_exists(INCLUDES["VIEWS"].REQ["PATH"]) || 
		   file_exists(INCLUDES["VIEWS"].REQ["PATH"]."/index.php"))) $response = "SYSTEM-200";
		if(REQ["PATH"] == "/api" && REQ["METHOD"] != "GET") $response = "THEME-API";
		if($info->type == "redirect") $response = "PAGE-301";
		
		// Setup the site constant
		define("Phroses\SITE", [
			"ID" => $info->id,
			"RESPONSE" => $response,
			"NAME" => $info->name,
			"THEME" => $info->theme,
			"USERNAME" => $info->adminUsername,
			"PASSWORD" => $info->adminPassword,
			"PAGE" => [
				"TYPE" => $info->type ?? "page",
				"TITLE" => $info->title,
				"CONTENT" => json_decode($info->content, true)
			]
		]);
	}
	
	static public function Render() {
		if(SITE["RESPONSE"] == "PAGE-301") {
			http_response_code(301);
			header("location: ".SITE["PAGE"]["CONTENT"]["destination"]);
			die;
		}
		
		ob_start("ob_gzhandler");
		$theme = new Theme(SITE["THEME"], SITE["PAGE"]["TYPE"]);
		if(SITE["RESPONSE"] == "PAGE-200") {
			$theme->title = SITE["PAGE"]["TITLE"];
			echo $theme;
		}
		
		if(SITE["RESPONSE"] == "SYSTEM-200") {
			if(!is_dir(INCLUDES["VIEWS"].REQ["PATH"]) && 
				file_exists(INCLUDES["VIEWS"].REQ["PATH"]) && 
				strtolower(REQ["EXTENSION"]) != "php") readfile(INCLUDES["VIEWS"].REQ["PATH"]);
			else {
				ob_start();
				if(!$_SESSION && REQ["PATH"] != "/admin/login") { 
					http_response_code(401);
?>				<form id="phroses-login">
						<h2>Login to Phroses Site Panel</h2>
						<div><input name="username" type="text" placeholder="Username"></div>
						<div><input name="password" type="password" placeholder="Password"></div>
						<div><input type="submit" value="Login"></div>
					</form>
				<? } else { 
					if(REQ["METHOD"] == "GET") { ?>
		
					<div class="dashbar">
						<div class="dashbar_brand">
							<a href="/admin">Phroses Panel</a>
						</div>
						<div class="dashbar_actions">
							<?= REQ["HOST"]; ?> | <a href="/admin/logout">Logout</a>
						</div>
						<div class="clear"></div>
					</div>
			
		<? 			}
					if(file_exists(INCLUDES["VIEWS"].REQ["PATH"]."/index.php")) include INCLUDES["VIEWS"].REQ["PATH"]."/index.php";
					else include INCLUDES["VIEWS"].REQ["PATH"].".php";
				}
				$theme->title = $title ?? "Phroses System Page";
				$theme->main = trim(ob_get_clean());
				$theme->Push("stylesheets", [ "src" => "/system.css" ]);
				$theme->Push("scripts", [ "src" => "//ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js", "attrs" => "async"]);
				$theme->Push("scripts", [ "src" => "/system.js", "attrs" => "async defer"]);
				echo $theme;
			}
		}
		
		if(SITE["RESPONSE"] == "THEME-API") {
			if(!$theme->HasAPI()) {
				$theme->title = "404 Not Found";
				$theme->main = "<h1>404 Not Found</h1><p>The page you are looking for could not be found.  Please check your spelling and try again.</p>";
				echo $theme;
			} else {
				$theme->RunAPI();
			}
		}
		
		if(SITE["RESPONSE"] == "UNDETERMINED") {
			if($theme->AssetExists(REQ["PATH"])) $theme->AssetRead(REQ["PATH"]); // Assets
			else if($theme->ErrorExists("404")) $theme->ErrorRead("404"); // Site-Level 404
			else { // Generic Site 404
				$theme->title = "404 Not Found";
				$theme->main = "<h1>404 Not Found</h1><p>The page you are looking for could not be found.  Please check your spelling and try again.</p>";
				echo $theme;
			}
		}
		
		session_write_close();
		ob_end_flush();
		flush();
	}
}

Phroses::start();