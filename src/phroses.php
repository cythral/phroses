<?php

namespace Phroses;

include __DIR__."/constants.php";
include SRC."/functions.php";

use \reqc;
use \reqc\Output;
use \listen\Events;
use \phyrex\Template;
use \inix\Config as inix;
use const \reqc\{ VARS, MIME_TYPES };

abstract class Phroses {
	static private $out;
	static private $handlers = [];
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
		"DEFAULT" => 0,

		"PAGE" => [
			200 => 1,
			301 => 2,
			404 => 3
		],

		"SYS" => [
			200 => 4
		],

		"THEME" => 5
	];

	static public function Start() {
		if(self::$ran) return; // only run once
		self::$out = new Output();
		
        self::LoadPlugins();
		if(!self::CheckReqs()) return;
		self::SetupMode();

		include SRC."/routes.php";

		if(reqc\TYPE != reqc\TYPES["CLI"]) {
			self::SetupSession();
            self::LoadSiteInfo();
			self::UrlFix();
			
			// route to proper response
			if(SITE["RESPONSE"] != self::RESPONSES["SYS"][200]) {
				(isset(self::$handlers[reqc\METHOD][SITE["RESPONSE"]])) ? self::$handlers[reqc\METHOD][SITE["RESPONSE"]]() : self::$handlers[reqc\METHOD][self::RESPONSES["DEFAULT"]]();
			} else self::$handlers["GET"][SITE["RESPONSE"]]();

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

	static public function route(string $method, int $response, callable $handler) {
		$method = strtoupper($method);
		if(!isset(self::$handlers[$method])) self::$handlers[$method] = [];
		self::$handlers[$method][$response] = $handler;
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