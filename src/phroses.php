<?php

/**
 * Entry point for phroses, all requests start at this file.
 * Phroses is a content dispatcher / router, it puts together a response
 * based on request variables (HTTP request headers, CLI args, etc.)
 * Request variables are retrieved using cythral/reqc (request controller).  
 */

namespace Phroses;

include __DIR__."/constants.php";

// setup autoloader, functions
$loader = include ((INPHAR) ? SRC : ROOT) . "/vendor/autoload.php";
$loader->addPsr4("Phroses\\", SRC."/models");
include SRC."/functions.php";

use \reqc;
use \reqc\Output;
use \listen\Events;
use \phyrex\Template;
use \inix\Config as inix;

// request variables
use const \reqc\{ VARS, MIME_TYPES, PATH, BASEURL, TYPE, TYPES, METHOD };

/**
 * This class is a collection of static methods and properties for
 * the sake of breaking up functional elements into smaller, more manageable units of code.
 * It is not meant to be instantiated, and should only be run once.  
 */
abstract class Phroses {
	
	static private $out;
	static private $routes = [];
	static private $cmds = [];
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

	const MM_ON = true;
	const MM_OFF = false;
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

	const METHODS = [
		"GET",
		"POST",
		"PATCH",
		"PUT",
		"DELETE"
	];
	
	/**
	 * This is the first method that gets run.  Triggers listen events
	 * which call other methods in the class.
	 */
	static public function start() {
		self::$out = new Output();

		Events::trigger("pluginsloaded", [ self::loadPlugins() ]);
		if(!Events::attach("reqscheck", [ INCLUDES["THEMES"]."/bloom", ROOT."/phroses.conf" ], "\Phroses\Phroses::checkReqs")) return;
		Events::attach("modeset", [ (bool) (inix::get("devnoindex") ?? true) ], "\Phroses\Phroses::setupMode");

		// page or asset
		if(TYPE != TYPES["CLI"]) {
			Events::trigger("routesmapped", [ include SRC."/routes.php" ]);
			Events::trigger("sessionstarted", [ Session::start() ]);
			Events::attach("siteinfoloaded", [ (bool)(inix::get("expose") ?? true) ], "\Phroses\Phroses::loadSiteInfo");
			if((bool) (inix::get("notrailingslashes") ?? true)) self::removeTrailingSlash();
			Events::attach("routesfollow", [ METHOD, self::$response ], "\Phroses\Phroses::followRoute");

		// command line
		} else {
			Events::trigger("commandsmapped", [ include SRC."/commands.php" ]);
			Events::attach("commandexec", [ $_SERVER["argv"] ?? [] ], "\Phroses\Phroses::executeCommand");
			exit(0);
		}
	}
	
	/**
	 * Loads, and sets up plugins from the plugins directory.
	 * 
	 * @return array a list of plugin names that were loaded
	 */
	static public function loadPlugins(): array {
		foreach(glob(INCLUDES["PLUGINS"]."/*", GLOB_ONLYDIR) as $dir) {
			static $list = [];
			if(file_exists("{$dir}/bootstrap.php")) include "{$dir}/bootstrap.php";
			$list[] = basename($dir);
		}

		return $list;
	}
	
	/**
	 * Sets up production / development mode
	 * Alters ini settings and removes x-robots-tag header if setup to do so
	 *
	 * @param bool $noindex removes x-robots-tag if true and in development mode
	 * @return bool if modesetting was successful
	 */
	static public function setupMode(bool $noindex): bool {
		if(!array_key_exists(inix::get("mode"), self::$modes)) return false;
		
		foreach(self::$modes[inix::get("mode")] as $key => $val) { 
			ini_set($key, $val); 
		}

		if($noindex && inix::get("mode") == "development") {
			self::$out->setHeader("X-Robots-Tag", "none");
		}
		
		return true;
	}
	
	/**
	 * Checks for environment and filesystem requirements.  Currently, this just
	 * checks for the default theme and the configuration file.
	 *
	 * @param string $defaultTheme the location of the default theme
	 * @param string $configFile the location of the config file
	 * @return bool true if environment/filesystem requirements have been met
	 */
	static public function checkReqs(string $defaultTheme, string $configFile) {
		if(!file_exists($defaultTheme)) {
			self::$out->setCode(500);
			$themeError = new Template(INCLUDES["TPL"]."/errors/notheme.tpl");
			$themeError->themename = basename($defaultTheme);
			echo $themeError;
			return false;
		}

		// if no configuration file found, run installer
		if(!inix::load($configFile)) {
			include SRC."/system/install.php";
			return false;
		}

		return true;
	}
	
	/**
	 * Loads information about a site.  Creates the SITE[] constant (which
	 * will be eventually phased out in favor of a class), as well as a Page
	 * object.
	 * 
	 * @param bool $showNewSite whether or not to show the form to create a new site
	 */
	static public function loadSiteInfo(bool $showNewSite) {
		$info = DB::query("SELECT `sites`.`id`, `sites`.`theme`, `sites`.`name`, `sites`.`adminUsername`, `sites`.`adminPassword`, `sites`.`adminURI`, `sites`.`maintenance`, `page`.`title`, `page`.`content`, (@pageid:=`page`.`id`) AS `pageID`, `page`.`type`, `page`.`views`, `page`.`public`, `page`.`dateCreated`, `page`.`dateModified` FROM `sites` LEFT JOIN `pages` AS `page` ON `page`.`siteID`=`sites`.`id` AND `page`.`uri`=? WHERE `sites`.`url`=?; UPDATE `pages` SET `views` = `views` + 1 WHERE `id`=@pageid;", [
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

		if(substr(PATH, 0, 8) == "/uploads" && file_exists(INCLUDES["UPLOADS"]."/".BASEURL."/".substr(PATH, 8)) && trim(PATH, "/") != "uploads") self::$response = self::RESPONSES["UPLOAD"];
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
		
		// make this into an object instead
		define("Phroses\SITE", [
			"ID" => $info->id,
			"NAME" => $info->name,
			"THEME" => $info->theme,
			"ADMINURI" => $info->adminURI ?? "/admin",
			"USERNAME" => $info->adminUsername,
			"PASSWORD" => $info->adminPassword,
			"PAGE" => $pageData,
			"MAINTENANCE" => $info->maintenance
		]);

		self::$page = new Page($pageData);
		if(in_array(self::$response, [ self::RESPONSES["MAINTENANCE"], self::RESPONSES["PAGE"][404] ]) && self::$page->theme->assetExists(PATH)) self::$response = self::RESPONSES["ASSET"];
		if(self::$response == self::RESPONSES["API"] && !self::$page->theme->hasApi()) self::$response = self::RESPONSES["PAGE"][404];
	}
	
	/**
	 * Removes the trailing slash from a uri (/admin/ => /admin)
	 */
	static public function removeTrailingSlash() {
		if(substr(PATH, -1) == "/" && PATH != "/") {
			self::$out->redirect(substr(PATH, 0, -1));
		}
	}
	
	/**
	 * Adds an HTTP route.  Routes should be added using this method in ./routes.php
	 * 
	 * @param ?string $method the http method/verb (get, post, put, etc.) leave null for all of them
	 * @param int $response the response identifier (see self::RESPONSES for acceptable responses)
	 * @param callable $handler the route handler, executed by followRoute
	 */
	static public function addRoute(?string $method, int $response, callable $handler) {
		$methods = ($method) ? [ strtoupper($method) ] : [ "GET", "POST", "PUT", "PATCH", "DELETE" ]; // if method was null, create a route for all methods

		foreach($methods as $method) {
			if(!isset(self::$routes[$method])) self::$routes[$method] = [];
			self::$routes[$method][$response] = $handler;
		}
	}
	
	/**
	 * "Follows" / executes an http route.  See ./routes.php for the different routes
	 *
	 * @param string $method the method to use for following the route (get, post, put, etc.)
	 * @param int $response the response to use (see self::RESPONSES for possible responses)
	 * @return mixed anything the route returned
	 */
	static public function followRoute(string $method, int $response) {
		if($response == self::RESPONSES["SYS"][200]) $method = "GET";
		if(!isset(self::$routes[$method][$response])) $response = self::RESPONSES["DEFAULT"];
		return self::$routes[$method][$response](self::$page);
	}
	
	/**
	 * Executes a cli command.  See ./commands.php for the different commands
	 * 
	 * @param array $cliArgs an array of arguments passed from the command line
	 */
	static public function executeCommand(array $cliArgs) {
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

		if(isset(self::$commands[$cmd])) self::$commands[$cmd]($args, $flags);
	}
	
	/**
	 * Adds a cli command.  Cli commands should be added using this method
	 * in ./commands.php
	 *
	 * @param string $cmd the name of the command
	 * @param callable $handler the command handler
	 */
	static public function addCmd(string $cmd, callable $handler) {
		self::$commands[$cmd] = $handler;
	}
	
	/**
	 * Turns application-wide maintenance mode off or on
	 *
	 * @param bool $mode whether to turn maintainenance mode off or on (use self::MM_ON or self::MM_OFF)
	 */
	static public function setMaintenance(bool $mode = self::MM_ON) {
		if($mode == self::MM_ON) copy(INCLUDES["TPL"]."/maintenance.tpl", ROOT."/.maintenance");
		if($mode == self::MM_OFF) unlink(ROOT."/.maintenance");
	}
}

// lets begin
Phroses::start();