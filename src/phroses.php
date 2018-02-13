<?php

namespace Phroses;

include __DIR__."/constants.php";
include SRC."/functions.php";

use \reqc;
use \reqc\Output;
use \listen\Events;
use \phyrex\Template;
use \inix\Config as inix;
use const \reqc\{ VARS, MIME_TYPES, PATH, BASEURL };

abstract class Phroses {
	static private $out;
	static private $handlers = [];
	static private $cmds = [];
	static private $ran = false;
	static private $page;
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

	static public $response = self::RESPONSES["PAGE"][200];


	const ON = true;
    const OFF = false;
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

		"ASSET" => 5,
		"API" => 6,
		"UPLOAD" => 7,
		"MAINTENANCE" => 8
	];

	static public function start() {
		if(self::$ran) return; // only run once
		self::$out = new Output();
		
		Events::trigger("pluginsloaded", [self::loadPlugins()]);
		if(!Events::attach("reqscheck", [ INCLUDES["THEMES"]."/bloom", ROOT."/phroses.conf" ], "\Phroses\Phroses::checkReqs")) return;
		Events::attach("modeset", [ (bool)(inix::get("devnoindex") ?? true) ], "\Phroses\Phroses::setupMode");
		
		DB::Update();

		// page or asset
		if(reqc\TYPE != reqc\TYPES["CLI"]) {
			Events::trigger("routesmapped", [ include SRC."/routes.php" ]);
			Events::trigger("sessionstarted", [self::setupSession()]);
            Events::attach("siteinfoloaded", [ (bool)(inix::get("expose") ?? true) ], "\Phroses\Phroses::loadSiteInfo");
			if(((bool)(inix::get("notrailingslashes") ?? true))) self::urlFix();
			Events::attach("routestrace", [ reqc\METHOD, self::$response ], "\Phroses\Phroses::traceRoutes");

		// command line
		} else {
			Events::trigger("commandsmapped", [ include SRC."/commands.php" ]);
            Events::attach("commandstrace", [ $_SERVER["argv"] ?? [] ], "\Phroses\Phroses::traceCommands");
			exit(0);
		}
	}

	static public function setupMode(bool $noindex) {
		if(self::$ran) return; // only run once
		if(!array_key_exists(inix::get("mode"), self::$modes)) return false;
        foreach(self::$modes[inix::get("mode")] as $key => $val) { ini_set($key, $val); }

        if($noindex && inix::get("mode") == "development") {
            self::$out->setHeader("X-Robots-Tag", "none");
        }
	}

    static public function loadPlugins(): array {
        foreach(glob(INCLUDES["PLUGINS"]."/*", GLOB_ONLYDIR) as $dir) {
			static $list = [];
			if(file_exists($dir."/bootstrap.php")) include $dir."/bootstrap.php";
			$list[] = basename($dir);
		}

		return $list;
	}

	static public function checkReqs(string $defaultTheme, string $configFile) {
		if(!file_exists($defaultTheme)) {
			self::$out->setCode(500);
			$themeError = new Template(INCLUDES["TPL"]."/errors/notheme.tpl");
			$themeError->themename = basename($defaultTheme);
			die($themeError);
		}
		
		// if no configuration file found, run installer
		if(!inix::load($configFile)) {
			include SRC."/system/install.php";
			return false;
		}

		return true;
	}

	static public function route(string $method, int $response, callable $handler, bool $json = false) {
		$method = strtoupper($method);
		if(!isset(self::$handlers[$method])) self::$handlers[$method] = [];
		self::$handlers[$method][$response] = $handler;
	}

	static public function addCmd(string $cmd, callable $handler) {
		self::$cmds[$cmd] = $handler;
	}


	static public function loadSiteInfo(bool $showNewSite) {
		if(self::$ran) return;

		$info = DB::Query("SELECT `sites`.`id`, `sites`.`theme`, `sites`.`name`, `sites`.`adminUsername`, `sites`.`adminPassword`, `sites`.`adminURI`, `sites`.`maintenance`, `page`.`title`, `page`.`content`, (@pageid:=`page`.`id`) AS `pageID`, `page`.`type`, `page`.`views`, `page`.`public`, `page`.`dateCreated`, `page`.`dateModified` FROM `sites` LEFT JOIN `pages` AS `page` ON `page`.`siteID`=`sites`.`id` AND `page`.`uri`=? WHERE `sites`.`url`=?; UPDATE `pages` SET `views` = `views` + 1 WHERE `id`=@pageid;", [
			PATH,
			BASEURL
		]);

		// if site doesn't exist, create a new one (script ends here)
		if(count($info) == 0) {
			if($showNewSite) include "system/newsite.php";
			else {
				self::$out->setCode(404);
				die(new Template(INCLUDES["TPL"]."/errors/nosite.tpl"));
			}
		}
		

		// Determine Response Type
		if(!isset(($info = $info[0])->pageID)) self::$response = self::RESPONSES["PAGE"][404];
		if(PATH != "/" && substr(PATH, 0, strlen($info->adminURI)) == $info->adminURI &&
			(file_exists(INCLUDES["VIEWS"].($adminpath = substr(PATH, strlen($info->adminURI))).".php") ||
		   	file_exists(INCLUDES["VIEWS"].$adminpath) ||
		   	file_exists(INCLUDES["VIEWS"]."$adminpath/index.php"))) self::$response = self::RESPONSES["SYS"][200];

		if(substr(PATH, 0, 8) == "/uploads" && file_exists(INCLUDES["UPLOADS"]."/".BASEURL."/".substr(PATH, 8))) self::$response = self::RESPONSES["UPLOAD"];
		if(substr(PATH, 0, 4) == "/api") self::$response = self::RESPONSES["API"];
		if($info->type == "redirect") self::$response = self::RESPONSES["PAGE"][301];
        if(self::$response == self::RESPONSES["PAGE"][200] && !$info->public && !$_SESSION) self::$response = self::RESPONSES["PAGE"][404];
		if($info->maintenance && !$_SESSION && self::$response != self::RESPONSES["SYS"][200]) self::$response = self::RESPONSES["MAINTENANCE"];

		$pageData = [
			"ID" => $info->pageID,
			"TYPE" => $info->type ?? "page",
			"VIEWS" => $info->views + 1,
			"DATECREATED" => $info->dateCreated,
			"DATEMODIFIED" => $info->dateModified,
			"TITLE" => $info->title,
			"CONTENT" => json_decode($info->content, true) ?? [],
			"VISIBILITY" => $info->public
		];

		// Setup the site constant
		// maybe should have this as an object instead?
		// todo: thinkabout that
		define("Phroses\SITE", [
			"ID" => $info->id,
			"RESPONSE" => self::$response,
			"NAME" => $info->name,
			"THEME" => $info->theme,
			"ADMINURI" => $info->adminURI ?? "/admin",
			"USERNAME" => $info->adminUsername,
			"PASSWORD" => $info->adminPassword,
			"PAGE" => $pageData,
			"MAINTENANCE" => $info->maintenance
		]);

		self::$page = new Page($pageData, self::$out);
		if(in_array(self::$response, [ self::RESPONSES["MAINTENANCE"], self::RESPONSES["PAGE"][404] ]) && self::$page->theme->assetExists(PATH)) self::$response = self::RESPONSES["ASSET"];
	}

	static public function urlFix() {
		if(substr(PATH, -1) == "/" && PATH != "/") {
			self::$out->redirect(substr(PATH, 0, -1));
		}
	}

	static public function setupSession(): string {
		return Session::start();
	}

    
    static public function setMaintenance(bool $mode = self::ON) {
        if($mode == self::ON) copy(INCLUDES["TPL"]."/maintenance.tpl", ROOT."/.maintenance");
        if($mode == self::OFF) unlink(ROOT."/.maintenance");
    }


    static public function handleEmail() {
        $data = file_get_contents("php://stdin");
        $m = new Parser((string)$data);

        Events::trigger("email", [
            (string)$m->headers['from'],
            (string)$m->headers['to'],
            (string)$m->headers['subject'],
            (string)$m->bodies['text/plain']
        ]);
	}
	
	static public function traceRoutes($method, $route) {
		if($route != self::RESPONSES["SYS"][200]) {
			(isset(self::$handlers[$method][$route])) ? self::$handlers[$method][$route](self::$page) : self::$handlers[$method][self::RESPONSES["DEFAULT"]](self::$page);
		} else self::$handlers["GET"][$route](self::$page);
	}

	static public function traceCommands($cliArgs) {
		array_shift($cliArgs); // remove filename
		if(count($cliArgs) == 0) {
			echo "no command given";
			exit(1);
		}

		$cmd = array_shift($cliArgs);
		$args = [];
		$flags = [];

		foreach($cliArgs as $part) {
			if(substr($part, 0, 2) == "--") $flags[] = $part;
			else {
				$subparts = explode("=", $part);
				if(count($subparts) > 1) $args[$subparts[0]] = $subparts[1];
				else $args[$subparts[0]] = true;
			}
		}
		
		if(isset(self::$cmds[$cmd])) self::$cmds[$cmd]($args, $flags);
	}

	static public function error(string $error, bool $check, ?array $extra = [], int $code = 400) {
		if(!(self::$out instanceof \reqc\JSON\Server)) self::$out = new \reqc\JSON\Server;
        if($check) self::$out->send(array_merge(["type" => "error", "error" => $error], $extra), $code);
    }
}


Phroses::start();