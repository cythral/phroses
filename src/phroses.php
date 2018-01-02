<?php

namespace Phroses;

include __DIR__."/constants.php";
include SRC."/functions.php";

use \reqc;
use \reqc\Output;
use \listen\Events;
use \inix\Config as inix;
use const \reqc\{ VARS, MIME_TYPES };

abstract class Phroses {
	static private $out;
	
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

		self::$out = new Output();
		
        self::LoadPlugins();
		if(!self::CheckReqs()) return;
		self::SetupMode();

		if(reqc\TYPE != reqc\TYPES["CLI"]) {
			self::SetupSession();
            self::LoadSiteInfo();
			self::UrlFix();
			
			if(SITE["RESPONSE"] != self::RESPONSES["SYS"][200]) {
				call_user_func("self::".reqc\METHOD);
			} else self::GET();

		} else {
            if(isset($_SERVER["argv"])) {
                switch($_SERVER["argv"][1]) {
                    case "update" :
                        DB::Update();
                        break;

                    case "maintenance=on" :
                        self::SetMaintenance(self::ON);
                        break;

                    case "maintenance=off" :
                        self::SetMaintenance(self::OFF);
                        break;

                    case "email" :
                        self::HandleEmail();
                        break;
                }
            }
			exit(0);
		}
	}

	static public function SetupMode() {
		if(self::$ran) return; // only run once
		if(!array_key_exists(inix::get("mode"), self::$modes)) return false;
        foreach(self::$modes[inix::get("mode")] as $key => $val) { ini_set($key, $val); }

        if(inix::get("mode") == "development") {
            self::$out->setHeader("X-Robots-Tag", "none");
        }
	}

    static public function LoadPlugins() {
        foreach(glob(INCLUDES["PLUGINS"]."/*", GLOB_ONLYDIR) as $dir) {
            if(file_exists($dir."/bootstrap.php")) include $dir."/bootstrap.php";
        }
	}

	static public function CheckReqs() {
        Events::trigger("checkReqs:start");

		if(!file_exists(INCLUDES["THEMES"]."/bloom")) {
			self::$out->setCode(500);
			self::$out->setContentType(MIME_TYPES["TXT"]);

			die("Default theme 'bloom' was not detected.  Please re-add the default bloom theme to its proper directory.");
		}
		
		// if no configuration file found, run installer
		if(!inix::load(ROOT."/phroses.conf")) {
			include SRC."/system/install.php";
			return false;
		}

        Events::trigger("checkReqs:end");
		return true;
	}

	static public function LoadSiteInfo() {
		if(self::$ran) return;

		if(reqc\TYPE == "cli") { // functionality tbd
			define("Phroses\SITE", ["RESPONSE" => "CLI"]);
			return;
		}


		$info = DB::Query("SELECT `sites`.`id`, `sites`.`theme`, `sites`.`name`, `sites`.`adminUsername`, `sites`.`adminPassword`, `page`.`title`, `page`.`content`, (@pageid:=`page`.`id`) AS `pageID`, `page`.`type`, `page`.`views`, `page`.`public`, `page`.`dateCreated`, `page`.`dateModified` FROM `sites` LEFT JOIN `pages` AS `page` ON `page`.`siteID`=`sites`.`id` AND `page`.`uri`=? WHERE `sites`.`url`=?; UPDATE `pages` SET `views` = `views` + 1 WHERE `id`=@pageid;", [
			reqc\PATH,
			reqc\BASEURL
		]);

		// if site doesn't exist, create a new one (script ends here)
		if(count($info) == 0) include "system/newsite.php";

		// Determine Response Type
		$response = self::RESPONSES["PAGE"][200];
		if(!isset(($info = $info[0])->pageID)) $response = self::RESPONSES["PAGE"][404];
		if(reqc\PATH != "/" &&
			 (file_exists(INCLUDES["VIEWS"].reqc\PATH.".php") ||
		   file_exists(INCLUDES["VIEWS"].reqc\PATH) ||
		   file_exists(INCLUDES["VIEWS"].reqc\PATH."/index.php") ||
		   substr(reqc\PATH, 0, 13) == "/admin/pages/")) $response = self::RESPONSES["SYS"][200];

		if(reqc\PATH == "/api" && reqc\METHOD != "GET") $response = self::RESPONSES["THEME"];
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
		if(substr(reqc\PATH, -1) == "/" && $_SERVER["REQUEST_URI"] != "/") {
			self::$out->setHeader("location", substr(reqc\PATH, 0, -1));
			die;
		}
	}

	static public function SetupSession() {
		Session::start();
		register_shutdown_function("session_write_close");
	}

	static public function GET() {
		ob_start("ob_gzhandler");
		$theme = new Theme(SITE["THEME"], SITE["PAGE"]["TYPE"]);

		$responses = [];
		
		$responses[self::RESPONSES["PAGE"][200]] = function(&$theme) {
			$theme->title = SITE["PAGE"]["TITLE"];
			self::$out->setHeader("x-test", "true");
		};

		$responses[self::RESPONSES["PAGE"][301]] = function($theme) {
			if(isset(SITE["PAGE"]["CONTENT"]["destination"])) {
				self::$out->setCode(301);
				self::$out->setHeader("location", SITE["PAGE"]["CONTENT"]["destination"]);
				
				die;
			} else echo "incomplete redirect"; // todo: add a fixer form here
		};

		$responses[self::RESPONSES["SYS"][200]] = function(&$theme) {
			if(!is_dir(INCLUDES["VIEWS"].reqc\PATH) &&
				file_exists(INCLUDES["VIEWS"].reqc\PATH) &&
				strtolower(reqc\EXTENSION) != "php") {
				ReadfileCached(INCLUDES["VIEWS"].reqc\PATH);

			} else {
				ob_start();
				if(!$_SESSION && reqc\PATH != "/admin/login") {
					$theme->push("stylesheets", [ "src" => "/phr-assets/css/main.css" ]);
					$theme->push("scripts", [ "src" => "/phr-assets/js/main.js", "attrs" => "defer" ]);
					self::$out->setCode(401);

?>				<form id="phroses-login">
						<h2>Login to Phroses Site Panel</h2>
						<div><input name="username" type="text" placeholder="Username"></div>
						<div><input name="password" type="password" placeholder="Password"></div>
						<div><input type="submit" value="Login"></div>
					</form>
				<? } else {
					if(reqc\METHOD == "GET") {
						$theme->push("stylesheets", [ "src" => "/phr-assets/css/main.css" ]);
						$theme->push("scripts", [ "src" => "/phr-assets/js/main.js", "attrs" => "defer" ]);
					?>
					<div class="dashbar">
						<div class="dashbar_brand">
							<a href="/admin">Phroses Panel</a>
						</div>
						<div class="dashbar_actions">
							<?= reqc\HOST; ?> | <a href="/admin/logout">Logout</a>
						</div>
						<div class="clear"></div>
					</div>

		<? 			}
					if(file_exists(INCLUDES["VIEWS"].reqc\PATH."/index.php")) include INCLUDES["VIEWS"].reqc\PATH."/index.php";
					else if(substr(reqc\PATH, 0, 13) == "/admin/pages/") {
						$_GET["id"] = substr(reqc\PATH, 13);
						include INCLUDES["VIEWS"]."/admin/pages/edit.php";
					} else if(file_exists(INCLUDES["VIEWS"].reqc\PATH.'.php')) include INCLUDES["VIEWS"].reqc\PATH.".php";
					else echo "resource not found";
				}

				if($theme->HasType("admin")) $theme->SetType("admin", true);
				$theme->title = $title ?? "Phroses System Page";
				$theme->main = trim(ob_get_clean());
				$theme->push("stylesheets", [ "src" => "//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" ]);
				$theme->push("scripts", [ "src" => "/phroses.js", "attrs" => "defer"]);
			}
		};

		$responses[self::RESPONSES["PAGE"][404]] = function(&$theme) {

			if($theme->AssetExists(reqc\PATH) && $_SERVER["REQUEST_URI"] != "/") {
				$theme->AssetRead(reqc\PATH); // Assets
			} else if($theme->ErrorExists("404")) {
				self::$out->setCode(404);
				self::$out->setContentType(MIME_TYPES["HTML"]);
				$theme->ErrorRead("404"); 
				die;

			} else { // Generic Site 404
				self::$out->setCode(404);
				self::$out->setContentType(MIME_TYPES["HTML"]);
				
				$theme->SetType("page", true);
				$theme->title = "404 Not Found";
				$theme->main = "<h1>404 Not Found</h1><p>The page you are looking for could not be found.  Please check your spelling and try again.</p>";
			}
		};

		$responses[self::RESPONSES["THEME"]] = function(&$theme) {
			if(!$theme->HasAPI()) {
				self::$out->setCode(404);
				$theme->title = "404 Not Found";
				$theme->main = "<h1>404 Not Found</h1><p>The page you are looking for could not be found.  Please check your spelling and try again.</p>";
			} else {
				$theme->RunAPI();
				die;
			}
		};

		$responses[SITE["RESPONSE"]]($theme);
		echo $theme;

		if(inix::get("mode") == "production") {
			ob_end_flush();
			flush();
		}
	}

	static public function POST() {
		self::$out = new reqc\JSON\Server();

        if(reqc\URI == "/api" && ($theme = new Theme(SITE["THEME"], "page"))->HasAPI()) {
            $theme->RunAPI();
            die;
		}
		
		unset($theme);

		// Validation
		if(!$_SESSION) self::$out->send(["type" => "error", "error" => "access_denied"], 401);
		
		foreach(["title","type"] as $type) {
			if(!in_array($type, array_keys($_REQUEST))) {
				self::$out->send([ "type" => "error", "error" => "missing_value", "field" => $type], 400);
			}
		}

		if(SITE["RESPONSE"] != self::RESPONSES["PAGE"][404]) self::$out->send([ "type" => "error", "error" => "resource_exists" ], 400);
		
		try { 
			$theme = new Theme(SITE["THEME"], $_REQUEST["type"]); 
		} catch(\Exception $e) { 
			self::$out->send(["type" => "error", "error" => "bad_value", "field" => "type" ], 400); 
		}

		DB::Query("INSERT INTO `pages` (`uri`,`title`,`type`,`content`, `siteID`,`dateCreated`) VALUES (?, ?, ?, ?, ?, NOW())", [
			reqc\PATH,
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

		self::$out->send($output, 200);
	}

	static public function PATCH() {
		self::$out = new reqc\JSON\Server();

		// Validation
		if(!$_SESSION) self::$out->send(["type" => "error", "error" => "access_denied"], 401);

		foreach(["id"] as $type) {
			if(!in_array($type, array_keys(VARS))) self::$out->send([ "type" => "error", "error" => "missing_value", "field" => $type], 400);
		}

		if(SITE["RESPONSE"] != self::RESPONSES["PAGE"][200] && SITE["RESPONSE"] != self::RESPONSES["PAGE"][301]) {
			self::$out->send([ "type" => "error", "error" => "resource_missing" ], 400);
		}

		try { 
			$theme = new Theme(SITE["THEME"], $_REQUEST["type"] ?? SITE["PAGE"]["TYPE"]); 
		} catch(\Exception $e) { 
			self::$out->send(["type" => "error", "error" => "bad_value", "field" => "type" ], 400); 
		}

		// if no change was made, dont update the db
		if(!isset($_REQUEST["type"]) && 
			!isset($_REQUEST["uri"]) && 
			!isset($_REQUEST["title"]) && 
			!isset($_REQUEST["content"]) && 
			!isset($_REQUEST["public"])) {
				self::$out->send(["type" => "error", "error" => "no_change" ], 400);
		}

		if(isset($_REQUEST["uri"])) {
			$count = DB::Query("SELECT COUNT(*) AS `count` FROM `pages` WHERE `siteID`=? AND `uri`=?", [ SITE["ID"], $_REQUEST["uri"]])[0] ?? 0;
			if($count->count > 0) self::$out->send(["type" => "error", "error" => "resource_exists"], 400);
		}

		// do NOT update the database if the request is to change the page to a redirect and there is no content specifying the destination.
		// if the page is a type redirect and there is no destination, an error will be displayed which we should be trying to avoid
		if(!(isset($_REQUEST["type"]) && $_REQUEST["type"] == "redirect" && (!isset($_REQUEST["content"]) || 
			(isset($_REQUEST["content"]) && !isset(json_decode($_REQUEST["content"])->destination))))) {

			DB::Query("UPDATE `pages` SET `title`=?, `uri`=?, `content`=?, `type`=?, `public`=? WHERE `id`=?", [
				$_REQUEST["title"] ?? SITE["PAGE"]["TITLE"],
				urldecode($_REQUEST["uri"] ?? reqc\URI),
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
		self::$out->send($output, 200);
	}

	static public function DELETE() {
		self::$out = new reqc\JSON\Server();
		if(!$_SESSION) self::$out->send(["type" => "error", "error" => "access_denied"], 401);
		if(SITE["RESPONSE"] != "PAGE-200" && SITE["RESPONSE"] != "PAGE-301") self::$out->send([ "type" => "error", "error" => "resource_missing" ], 400);

		DB::Query("DELETE FROM `pages` WHERE `uri`=? AND `siteID`=?", [ reqc\PATH, SITE["ID"] ]);
		self::$out->send(["type" => "success"], 200);
	}

    const ON = true;
    const OFF = false;
    static public function SetMaintenance(bool $mode = self::ON) {
        if($mode == self::ON) copy(INCLUDES["TPL"]."/maintenance.tpl", ROOT."/.maintenance");
        if($mode == self::OFF) unlink(ROOT."/.maintenance");
    }


    static public function HandleEmail() {
        $data = file_get_contents("php://stdin");
        $m = new Parser((string)$data);

        Events::trigger("email", [
            (string)$m->headers['from'],
            (string)$m->headers['to'],
            (string)$m->headers['subject'],
            (string)$m->bodies['text/plain']
        ]);
    }
}

Phroses::start();