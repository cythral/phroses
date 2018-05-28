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
use \Throwable;

// phroses core
use \Phroses\Modes\Mode;
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
 * front controller style
 */
abstract class Phroses {
	
	static private $out;
	static private $db;
	static private $mode;

	static public $configFileLoaded = false;
	static public $page;
	static public $site;

	static public $response = RouteController::RESPONSES["PAGE"][200];

	const SITE_COLS = 9;
	const MM_ON = true;
	const MM_OFF = false;
	
	/**
	 * This is the first method that gets run.  Triggers listen events
	 * which call other methods in the class.
	 * 
	 * @return void
	 */
	static public function main(): void {
		set_exception_handler("\Phroses\Phroses::exceptionHandler");
		Events::trigger("pluginsloaded", [ Plugin::loadAll() ]);

		self::$out = new Output;
		self::$configFileLoaded = Events::attach("reqscheck", [ CONF_ROOT."/phroses.conf" ], "\Phroses\Phroses::checkReqs");
		self::$mode = Events::attach("modeset", [ inix::get("mode"), (bool) (inix::get("devnoindex") ?? true) ], "\Phroses\Phroses::modeSet");

		$dbconfig = inix::get(self::$mode->dbDirective);
		Events::attach("dbsetup", [ $dbconfig["host"], $dbconfig["name"], $dbconfig["user"], $dbconfig["password"] ], "\Phroses\Phroses::setupDatabase");

		(new Switcher(TYPE))
			->case(TYPES["HTTP"], "\Phroses\Phroses::http")
			->case(TYPES["CLI"], "\Phroses\Phroses::cli");
	}

	/**
	 * The default exception handler
	 * 
	 * @return void
	 */
	static public function exceptionHandler(Throwable $e): void {
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
	}

	/**
	 * Handles HTTP Responses
	 * 
	 * @return void
	 */
	static public function http(): void {
		if(!self::$configFileLoaded) return;
		Events::trigger("sessionstarted", [ Session::start() ]);

		if(self::isMaintenanceModeOn() && !isset($_SESSION)) {
			throw new ExitException(2, file_get_contents(DATA_ROOT."/.maintenance"));
		}
		
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
	}

	/**
	 * Handles CLI Responses
	 * 
	 * @return void
	 */
	static public function cli(): void {
		array_shift($_SERVER["argv"]); // remove file/command name
		if(!isset($_SERVER["argv"][0])) throw new ExitException(1, "No Command Specified\n");

		$commandController = new CommandController;
		Events::attach("commandsmapped", [ include SRC."/commands.php" ], [$commandController, "addCommands"]);
		Events::attach("commandexec", [ array_shift($_SERVER["argv"]), $_SERVER["argv"] ?? [] ], [$commandController, "execute"]);
		throw new ExitException(0);
	}
	
	/**
	 * Sets up production / development mode
	 * Alters ini settings and removes x-robots-tag header if setup to do so
	 *
	 * @param string $modeName the name of the mode to set it to
	 * @param bool $noindex removes x-robots-tag if true and in development mode
	 * @return Mode the mode that was set
	 */
	static public function modeSet(string $modeName, bool $noindex): Mode {
		$modeClass = Mode::MODES[strtoupper($modeName)];
		$mode = new $modeClass;
		$mode->setup($noindex);

		return $mode;
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
	static public function checkReqs(string $configFile) {
		// if no configuration file found, run installer
		if(!inix::load($configFile)) {
			if(TYPE == TYPES["HTTP"]) {
				throw new ExitException(127, getIncludeOutput(SRC."/system/install.php"));
			}
			return false;
		}

		if(safeArrayValEquals($_REQUEST, "error", "rewrite")) {
			throw new ExitException(1, new Template(INCLUDES["TPL"]."/errors/rewrite.tpl"));
		}

		return true;
	}

	
	
	/**
	 * Loads information about the site and page requested
	 * 
	 * @param bool $showNewSite whether or not to show the form to create a new site
	 */
	static public function loadSiteInfo(bool $showNewSite) {
		$query = self::$db->fetch("CALL `viewPage`(?,?)", [ BASEURL, PATH ], PDO::FETCH_ASSOC);
		$info = $query[0] ?? null;

		// if site doesn't exist, create a new one
		if(!$info) {
			throw new ExitException(127, ($showNewSite) ? getIncludeOutput(SRC."/system/newsite.php") : new Template(INCLUDES["TPL"]."/errors/nosite.tpl"));
		}

		// first half of results are site columns, second half is page columns
		$siteInfo = array_slice($info, 0, self::SITE_COLS); 
		$pageInfo = array_slice($info, self::SITE_COLS);

		// id column was overwritten with pages.id
		$pageInfo["id"] = $siteInfo["id"];
		$siteInfo["id"] = $pageInfo["siteID"];

		// add defaults
		$pageInfo["content"] = json_decode($pageInfo["content"] ?? "{}", true);
		$pageInfo["type"] = empty($pageInfo["type"]) ? "page" : $pageInfo["type"];

		self::$site = new Site($siteInfo);
		self::$page = new Page($pageInfo, self::$site->theme);
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
		if($mode == self::MM_ON) copy(INCLUDES["TPL"]."/maintenance.tpl", DATA_ROOT."/.maintenance");
		if($mode == self::MM_OFF) unlink(DATA_ROOT."/.maintenance");
	}

	static public function isMaintenanceModeOn(): bool {
		return file_exists(DATA_ROOT."/.maintenance");
	}
}


Phroses::main();