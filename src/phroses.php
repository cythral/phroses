<?php

namespace Phroses;

include __DIR__."/constants.php";
include SRC."/functions.php";

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
	
	const RESPONSES = [
		"PAGE" => [ 
			200 => 0, 
			301 => 1,
			404 => 2
		],
		
		"SYS" => [
			200 => 3
		],
		
		"THEME" => 4
	];
	
	static public function Start() {
		if(self::$ran) return; // only run once

		self::LoadModels();
		if(!self::CheckReqs()) return;
		self::SetupMode();
		if(REQ["TYPE"] != "cli") {
			self::SetupSession();
            self::LoadSiteInfo();
            self::UrlFix();
			if(SITE["RESPONSE"] != self::RESPONSES["SYS"][200]) call_user_func("self::".REQ["METHOD"]);
			else self::GET();
			
		} else {
			if(isset($_SERVER["argv"][1]) && $_SERVER["argv"][1] == "update") DB::Update();
			else if(isset($_SERVER["argv"][1]) && $_SERVER["argv"][1] == "maintenance=on") self::SetMaintenance(self::ON);
			else if(isset($_SERVER["argv"][1]) && $_SERVER["argv"][1] == "maintenance=off") self::SetMaintenance(self::OFF);
			exit(0);
		}
	}
	
	static public function SetupMode() {
		if(self::$ran) return; // only run once
		if(!array_key_exists(Config::Get("mode"), self::$modes)) return false;
        foreach(self::$modes[Config::Get("mode")] as $key => $val) { ini_set($key, $val); }
        
        if(Config::Get("mode") == "development") {
            header("X-Robots-Tag: none");
        }
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
	
	static public function CheckReqs() {
		if(!file_exists(INCLUDES["THEMES"]."/bloom")) {
			http_response_code(500);
			header("content-type: text/plain");
			echo "Default theme 'bloom' was not detected.  Please re-add the default bloom theme to its proper directory.";
			exit(1);
		}

		// if no configuration file found, run installer
		if(!Config::Load()) {
			include SRC."/system/install.php";
			return false;
		}
		return true;
	}
	
	static public function LoadSiteInfo() {
		if(self::$ran) return;
		
		if(REQ["TYPE"] == "cli") { // functionality tbd
			define("Phroses\SITE", ["RESPONSE" => "CLI"]);
			return;
		}
		
		$info = DB::Query("SELECT `sites`.`id`, `sites`.`theme`, `sites`.`name`, `sites`.`adminUsername`, `sites`.`adminPassword`, `page`.`title`, `page`.`content`, (@pageid:=`page`.`id`) AS `pageID`, `page`.`type`, `page`.`views`, `page`.`public`, `page`.`dateCreated`, `page`.`dateModified` FROM `sites` LEFT JOIN `pages` AS `page` ON `page`.`siteID`=`sites`.`id` AND `page`.`uri`=? WHERE `sites`.`url`=?; UPDATE `pages` SET `views` = `views` + 1 WHERE `id`=@pageid;", [
			REQ["PATH"],
			REQ["BASEURL"]
		]);
		
		// if site doesn't exist, create a new one (script ends here)
		if(count($info) == 0) include "system/newsite.php";
		
		// Determine Response Type
		$response = self::RESPONSES["PAGE"][200];
		if(!isset(($info = $info[0])->pageID)) $response = self::RESPONSES["PAGE"][404];
		if(REQ["PATH"] != "/" && 
			 (file_exists(INCLUDES["VIEWS"].REQ["PATH"].".php") || 
		   file_exists(INCLUDES["VIEWS"].REQ["PATH"]) || 
		   file_exists(INCLUDES["VIEWS"].REQ["PATH"]."/index.php") ||
		   substr(REQ["PATH"], 0, 13) == "/admin/pages/")) $response = self::RESPONSES["SYS"][200];
		
		if(REQ["PATH"] == "/api" && REQ["METHOD"] != "GET") $response = self::RESPONSES["THEME"];
		if($info->type == "redirect") $response = self::RESPONSES["PAGE"][301];
        if($response == self::RESPONSES["PAGE"][200] && !$info->public && !$_SESSION) $response = self::RESPONSES["PAGE"][404]; 
		
        
		// Setup the site constant
		// maybe should have this as an object instead?
		// todo: thinkabout that
		define("Phroses\SITE", [
			"ID" => $info->id,
			"RESPONSE" => $response,
			"NAME" => $info->name,
			"THEME" => $info->theme,
			"USERNAME" => $info->adminUsername, // todo: remove username and password from constant cause security
			"PASSWORD" => $info->adminPassword,
			"PAGE" => [
				"ID" => $info->pageID,
				"TYPE" => $info->type ?? "page",
				"VIEWS" => $info->views,
                "DATECREATED" => $info->dateCreated,
                "DATEMODIFIED" => $info->dateModified,
				"TITLE" => $info->title,
				"CONTENT" => json_decode($info->content, true),
                "VISIBILITY" => $info->public
			]
		]);
	}
	
	static public function UrlFix() {
		if(substr(REQ["PATH"], -1) == "/" && $_SERVER["REQUEST_URI"] != "/") {
			header("location: ".substr(REQ["PATH"], 0, -1));
			die;
		}
	}
	
	static public function SetupSession() {
		Session::start();
		register_shutdown_function(function() {
			session_write_close();
		});
	}
	
	static public function GET() {
		ob_start("ob_gzhandler");
		$theme = new Theme(SITE["THEME"], SITE["PAGE"]["TYPE"]);
		
		[
			self::RESPONSES["PAGE"][200] => function(&$theme) {
				$theme->title = SITE["PAGE"]["TITLE"];
			},
			
			self::RESPONSES["PAGE"][301] => function($theme) {
                
                if(isset(SITE["PAGE"]["CONTENT"]["destination"])) {
                    http_response_code(301);
                    header("location: ".SITE["PAGE"]["CONTENT"]["destination"]);
                    die;
                } else echo "incomplete redirect"; // todo: add a fixer form here
			},
		
			self::RESPONSES["SYS"][200] => function(&$theme) {
				if(!is_dir(INCLUDES["VIEWS"].REQ["PATH"]) && 
					file_exists(INCLUDES["VIEWS"].REQ["PATH"]) && 
					strtolower(REQ["EXTENSION"]) != "php") { 
					readfile(INCLUDES["VIEWS"].REQ["PATH"]);
					die;
				} else {
					ob_start();
					if(!$_SESSION && REQ["PATH"] != "/admin/login") { 
                        $theme->Push("stylesheets", [ "src" => "/phr-assets/css/main.css" ]);
                        $theme->Push("scripts", [ "src" => "/phr-assets/js/main.js", "attrs" => "defer" ]);
						http_response_code(401);
	?>				<form id="phroses-login">
							<h2>Login to Phroses Site Panel</h2>
							<div><input name="username" type="text" placeholder="Username"></div>
							<div><input name="password" type="password" placeholder="Password"></div>
							<div><input type="submit" value="Login"></div>
						</form>
					<? } else { 
						if(REQ["METHOD"] == "GET") { 
							$theme->Push("stylesheets", [ "src" => "/phr-assets/css/main.css" ]);
							$theme->Push("scripts", [ "src" => "/phr-assets/js/main.js", "attrs" => "defer" ]);
						?>
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
						else if(substr(REQ["PATH"], 0, 13) == "/admin/pages/") {
							$_GET["id"] = substr(REQ["PATH"], 13);
							include INCLUDES["VIEWS"]."/admin/pages/edit.php";
						} else if(file_exists(INCLUDES["VIEWS"].REQ['PATH'].'.php')) include INCLUDES["VIEWS"].REQ["PATH"].".php";
						else echo "resource not found";
					}
                    
                    if($theme->HasType("admin")) $theme->SetType("admin", true);
					$theme->title = $title ?? "Phroses System Page";
					$theme->main = trim(ob_get_clean());
					$theme->Push("stylesheets", [ "src" => "//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" ]);
					$theme->Push("scripts", [ "src" => "/phroses.js", "attrs" => "defer"]);
				}
			},
		
			self::RESPONSES["PAGE"][404] => function(&$theme) {
				if($theme->AssetExists(REQ["PATH"]) && $_SERVER["REQUEST_URI"] != "/") {
                    $theme->AssetRead(REQ["PATH"]); // Assets
                    die;
                } else if($theme->ErrorExists("404")) { 
                    header("content-type: text/html");
                    $theme->ErrorRead("404"); die; 
                } else { // Generic Site 404
                    header("content-type: text/html");
                    $theme->SetType("page", true);
					$theme->title = "404 Not Found";
					$theme->main = "<h1>404 Not Found</h1><p>The page you are looking for could not be found.  Please check your spelling and try again.</p>";
				}
			},
		
			self::RESPONSES["THEME"] => function(&$theme) {
				if(!$theme->HasAPI()) {
					$theme->title = "404 Not Found";
					$theme->main = "<h1>404 Not Found</h1><p>The page you are looking for could not be found.  Please check your spelling and try again.</p>";
				} else {
					$theme->RunAPI();
					die;
				}
			}
		
		][SITE["RESPONSE"]]($theme);
		echo $theme;
		
		if(Config::Get("mode") == "production") {
			ob_end_flush();
			flush();
		}
	}
	
	static public function POST() {
        if(REQ["URI"] == "/api" && ($theme = new Theme(SITE["THEME"], "page"))->HasAPI()) {
            $theme->RunAPI();
            die;
        }
        unset($theme);
        
		// Validation
		if(!$_SESSION) JsonOutput(["type" => "error", "error" => "access_denied"], 401);
		foreach(["title","type"] as $type)
			if(!in_array($type, array_keys($_REQUEST))) JsonOutput([ "type" => "error", "error" => "missing_value", "field" => $type]);
		if(SITE["RESPONSE"] != self::RESPONSES["PAGE"][404]) JsonOutput([ "type" => "error", "error" => "resource_exists" ]);
		try { $theme = new Theme(SITE["THEME"], $_REQUEST["type"]); }
		catch(\Exception $e) { JsonOutput(["type" => "error", "error" => "bad_value", "field" => "type" ]); }
		
		DB::Query("INSERT INTO `pages` (`uri`,`title`,`type`,`content`, `siteID`,`dateCreated`) VALUES (?, ?, ?, ?, ?, NOW())", [ 
			REQ["PATH"],
			$_REQUEST["title"],
			$_REQUEST["type"],
			$_REQUEST["content"] ?? "{}",
			SITE["ID"]
		]);	
		
		$output = [ "type" => "success", "id" => DB::LastID(), "content" => $theme->GetBody() ];
		
		ob_start();
		foreach($theme->GetContentFields($_REQUEST["type"]) as $key => $field) { 
			if($field == "editor")  { ?><div class="form_field content editor" id="<?= $_REQUEST["type"] ?>-main" data-id="<?= $key; ?>"></div><? }
			else if(in_array($field, ["text", "url"])) { ?><input id="<?= $key; ?>" placeholder="<?= $key; ?>" type="<?= $field; ?>" class="form_input form_field content" value=""><? }	
		}
		$output["typefields"] = trim(ob_get_clean());
		
		JsonOutputSuccess($output);
	}
	
	static public function PATCH() {
		// Validation
		if(!$_SESSION) JsonOutput(["type" => "error", "error" => "access_denied"], 401);
		foreach(["id"] as $type)
			if(!in_array($type, array_keys($_REQUEST))) JsonOutput([ "type" => "error", "error" => "missing_value", "field" => $type]);
		
		if(SITE["RESPONSE"] != self::RESPONSES["PAGE"][200] && SITE["RESPONSE"] != self::RESPONSES["PAGE"][301]) JsonOutput([ "type" => "error", "error" => "resource_missing" ]);
		try { $theme = new Theme(SITE["THEME"], $_REQUEST["type"] ?? SITE["PAGE"]["TYPE"]); }
		catch(\Exception $e) { JsonOutput(["type" => "error", "error" => "bad_value", "field" => "type" ]); }
		
		// if no change was made, dont update the db
		if(!isset($_REQUEST["type"]) && !isset($_REQUEST["uri"]) && !isset($_REQUEST["title"]) && !isset($_REQUEST["content"]) && !isset($_REQUEST["public"]))
			JsonOutput(["type" => "error", "error" => "no_change" ]);
		
		if(isset($_REQUEST["uri"])) {
			$count = DB::Query("SELECT COUNT(*) AS `count` FROM `pages` WHERE `siteID`=? AND `uri`=?", [ SITE["ID"], $_REQUEST["uri"]])[0] ?? 0;
			if($count->count > 0) JsonOutput(["type" => "error", "error" => "resource_exists"]);
		}
		
		// do NOT update the database if the request is to change the page to a redirect and there is no content specifying the destination.
		// if the page is a type redirect and there is no destination, an error will be displayed which we should be trying to avoid
		if(!(isset($_REQUEST["type"]) && $_REQUEST["type"] == "redirect" && (!isset($_REQUEST["content"]) || (isset($_REQUEST["content"]) && !isset(json_decode($_REQUEST["content"])->destination))))) {
			
			DB::Query("UPDATE `pages` SET `title`=?, `uri`=?, `content`=?, `type`=?, `public`=? WHERE `id`=?", [
				$_REQUEST["title"] ?? SITE["PAGE"]["TITLE"], 
				urldecode($_REQUEST["uri"] ?? REQ["URI"]), 
				(isset($_REQUEST["type"]) && $_REQUEST["type"] != "redirect") ? "{}" : (htmlspecialchars_decode($_REQUEST["content"] ?? json_encode(SITE["PAGE"]["CONTENT"]))), 
				urldecode($_REQUEST["type"] ?? SITE["PAGE"]["TYPE"]),
                $_REQUEST["public"] ?? SITE["PAGE"]["VISIBILITY"],
				(int)$_REQUEST["id"]
			]);
		}
		
		$output = [ "type" => "success" ];
        if(!isset($_REQUEST["nocontent"])) $output["content"] = $theme->GetBody();
		if(isset($_REQUEST["type"])) {
			
			ob_start();
			foreach($theme->GetContentFields($_REQUEST["type"]) as $key => $field) { 
				if($field == "editor")  { ?><div class="form_field content editor" id="<?= $_REQUEST["type"] ?>-main" data-id="<?= $key; ?>"></div><? }
				else if(in_array($field, ["text", "url"])) { ?><input id="<?= $key; ?>" placeholder="<?= $key; ?>" type="<?= $field; ?>" class="form_input form_field content" value=""><? }	
			}
			$output["typefields"] = trim(ob_get_clean());
		}
		
		// if we are changing to type redirect or the page is a redirect, there is no content
		if(SITE["PAGE"]["TYPE"] == "redirect" || (isset($_REQUEST["type"]) && $_REQUEST["type"] == "redirect")) unset($output["content"]);
		JsonOutputSuccess($output);
	}
	
	static public function DELETE() {
		if(!$_SESSION) JsonOutput(["type" => "error", "error" => "access_denied"], 401);
		if(SITE["RESPONSE"] != "PAGE-200" && SITE["RESPONSE"] != "PAGE-301") JsonOutput([ "type" => "error", "error" => "resource_missing" ]);
		
		DB::Query("DELETE FROM `pages` WHERE `uri`=? AND `siteID`=?", [ REQ["PATH"], SITE["ID"] ]);
		JsonOutputSuccess();
	}
    
    const ON = true;
    const OFF = false;
    static public function SetMaintenance(bool $mode = self::ON) {
        if($mode == self::ON) copy(INCLUDES["TPL"]."/maintenance.tpl", ROOT."/.maintenance");
        if($mode == self::OFF) unlink(ROOT."/.maintenance");
    }
}

Phroses::start();