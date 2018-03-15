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
$loader->addPsr4("Phroses\\", SRC."/models"); // can't do this in composer.json because the location changes if in a phar
include SRC."/functions.php";

// php core
use \PDO;

// phroses core
use \Phroses\Plugins\Plugin;
use \Phroses\Theme\Theme;
use \Phroses\Database\Database;
use \Phroses\Routes\Controller as RouteController;
use \Phroses\Commands\Controller as CommandController;
use \Phroses\Exceptions\ExitException;

// vendor
use \inix\Config as inix;
use \reqc;
use \reqc\Output;
use \listen\Events;
use \phyrex\Template;

// request variables
use const \reqc\{ VARS, MIME_TYPES, PATH, BASEURL, TYPE, TYPES, METHOD };

/**
 * This class is a collection of static methods and properties for
 * the sake of breaking up functional elements into smaller, more manageable units of code.
 * It is not meant to be instantiated, and should only be run once.  
 */
abstract class Phroses {
	
	static private $out;
	static private $cascade;
	static private $db;

	static public $configFileLoaded = false;
	static public $page;
	static public $site;

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

	static public $response = RouteController::RESPONSES["PAGE"][200];

	const MM_ON = true;
	const MM_OFF = false;
	
	/**
	 * This is the first method that gets run.  Triggers listen events
	 * which call other methods in the class.
	 */
	static public function start() {
		self::$out = new Output;

		Events::attach("exceptionhandlerset", [], "\Phroses\Phroses::setExceptionHandler");
		Events::trigger("pluginsloaded", [ Plugin::loadAll() ]);
		self::$configFileLoaded = Events::attach("reqscheck", [ INCLUDES["THEMES"]."/bloom", ROOT."/phroses.conf" ], "\Phroses\Phroses::checkReqs");
		Events::attach("modeset", [ (bool) (inix::get("devnoindex") ?? true) ], "\Phroses\Phroses::setupMode");

		// setup database
		$dbconfig = ((defined("Phroses\TESTING") && inix::get("test-database")) ? inix::get("test-database") : inix::get("database"));
		Events::attach("dbsetup", [ $dbconfig["host"], $dbconfig["name"], $dbconfig["user"], $dbconfig["password"] ], "\Phroses\Phroses::setupDatabase");

		(new Switcher(TYPE))

		->case(TYPES["HTTP"], function() {
			if(!self::$configFileLoaded) return;

			Events::trigger("sessionstarted", [ Session::start() ]);
			Events::attach("siteinfoloaded", [ (bool)(inix::get("expose") ?? true) ], "\Phroses\Phroses::loadSiteInfo");
			if((bool) (inix::get("notrailingslashes") ?? true)) self::removeTrailingSlash();

			// setup routes
			$routeController = new RouteController;
			$routeController->addRuleArgs(self::$page, self::$site);
			Events::attach("routesmapped", [ include SRC."/routes.php" ], [$routeController, "addRoutes"]);
			self::$response = $routeController->getResponse();
			
			$routeController
				->select()
				->follow(self::$page, self::$site, self::$out);
		})

		->case(TYPES["CLI"], function() {
			array_shift($_SERVER["argv"]); // remove filename/command name

			$commandController = new CommandController;
			Events::attach("commandsmapped", [ include SRC."/commands.php" ], [$commandController, "addCommands"]);
			Events::attach("commandexec", [ array_shift($_SERVER["argv"]), $_SERVER["argv"] ?? [] ], [$commandController, "execute"]);
			throw new ExitException(0);
		});
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
	 * Sets up the database
	 * 
	 * @return void
	 */
	static public function setupDatabase(string $host, string $name, string $user, string $password): void {
		self::$db = Database::getInstance($host, $name, $user, $password);
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
			if(TYPE == TYPES["HTTP"]) {
				include SRC."/system/install.php";
			}
			
			return false;
		}

		if(safeArrayValEquals($_REQUEST, "error", "rewrite")) {
			self::$out->setCode(500);
			die((string) new Template(INCLUDES["TPL"]."/errors/rewrite.tpl"));
		}

		return true;
	}

	/**
	 * Sets the default exception handler
	 */
	static public function setExceptionHandler() {
		set_exception_handler(function(\Throwable $e) {
			if(method_exists($e, "defaultHandler")) {
				$e->defaultHandler();
			} else {
				(new Switcher(TYPE, [ $e ]))

				->case(TYPES["HTTP"], function($e) {
					$out = new Template(INCLUDES["TPL"]."/errors/exception.tpl");
					$out->message = inix::get("mode") == "production" ? "And could not continue." : ($e->getMessage() . "<br>" . $e->getTraceAsString());
					echo $out;
				})
				
				->case(TYPES["CLI"], function($e) {
					println($e->getMessage());
				});
				
			}
		});
	}
	
	/**
	 * Loads information about the site and page requested
	 * 
	 * @param bool $showNewSite whether or not to show the form to create a new site
	 */
	static public function loadSiteInfo(bool $showNewSite) {
		$query = self::$db->fetch("CALL `viewPage`(?,?)", [ BASEURL, PATH ]);
		$info = $query[0] ?? null;
		
		// if site doesn't exist, create a new one (script ends here)
		if(!$info) {
			if($showNewSite) include "system/newsite.php";
			else {
				self::$out->setCode(404);
				die(new Template(INCLUDES["TPL"]."/errors/nosite.tpl"));
			}
		}

		self::$site = new Site([
			"id" => $info->id,
			"url" => BASEURL,
			"name" => $info->name,
			"theme" => $info->theme,
			"adminURI" => $info->adminURI ?? "/admin",
			"adminUsername" => $info->adminUsername,
			"adminPassword" => $info->adminPassword,
			"adminIP" => $info->adminIP,
			"maintenance" => (bool)$info->maintenance
		]);
		
		self::$page = new Page([
			"id" => $info->pageID,
			"type" => $info->type ?? "page",
			"views" => $info->views,
			"dateCreated" => $info->dateCreated,
			"dateModified" => $info->dateModified,
			"title" => $info->title,
			"content" => json_decode($info->content, true) ?? [],
			"public" => $info->public,
			"css" => $info->css
		], self::$site->theme);
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