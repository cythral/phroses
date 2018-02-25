<?php
/**
 * This file sets up phroses' routing / method mapping.  This is included
 * from within the start method of the phroses class, so self here refers to 
 * \Phroses\Phroses
 */

namespace Phroses;

use \reqc;
use \reqc\Output;
use \reqc\JSON\Server as JSONServer;
use \listen\Events;
use \inix\Config as inix;
use \phyrex\Template;
use \Phroses\Theme\Theme;

// request variables
use const \reqc\{ VARS, MIME_TYPES, PATH, EXTENSION, METHOD, HOST, BASEURL };

/**
 * GET PAGE/200
 * This route gets page information and either displays it as html or json
 */
self::addRoute(new class extends Route {
	public $method = "get";
	public $response = Phroses::RESPONSES["PAGE"][200];

	public function follow(&$page, &$site, &$out) {
		
		if(safeArrayValEquals($_GET, "mode", "json")) {
			$out = new JSONServer();
			$out->send($page->getData(), 200);
		}

		$page->display();
	}
});

/**
 * GET PAGE/301
 * This route redirects to a different page.  If the destination is not specified, an error is displayed instead
 */
self::addRoute(new class extends Route {
	public $method = "get";
	public $response = Phroses::RESPONSES["PAGE"][301];

	public function follow(&$page, &$site, &$out) {

		if(array_key_exists("destination", $page->content) && !empty($page->content["destination"]) && $page->content["destination"] != PATH) {
			$out->redirect($page->content["destination"]);
		} 
		
		$page->theme->setType("page", true);
		$page->display([ "main" => (string) new Template(INCLUDES["TPL"]."/errors/redirect.tpl") ]);

	}

	public function rules(&$page, &$site, &$cascade) {
		return [ 
			4 => function() use (&$page) { 
				return $page->type == "redirect"; 
			} 
		];
	}
});

/**
 * GET SYS/200
 * Displays an internal phroses "view" (can be a dashboard page or asset file)
 */
self::addRoute(new class extends Route {
	public $method = "get";
	public $response = Phroses::RESPONSES["SYS"][200];

	public function follow(&$page, &$site, &$out) {
		$path = substr(PATH, strlen($site->adminURI));

		if(!is_dir($file = INCLUDES["VIEWS"].$path) && file_exists($file) && strtolower(EXTENSION) != "php") {
			readfileCached($file);
		}

		ob_start();
		$page->theme->push("stylesheets", [ "src" => $site->adminURI."/assets/css/main.css" ]);
		$page->theme->push("stylesheets", [ "src" => "//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" ]);
		$page->theme->push("scripts", [ "src" => $site->adminURI."/assets/js/main".(inix::get("mode") == "production" ? ".min" : "").".js", "attrs" => 'defer data-adminuri="'.$site->adminURI.'" id="phroses-script"' ]);

		if(!$_SESSION) {
			$out->setCode(401);
			include INCLUDES["VIEWS"]."/login.php";
		
		} else {
			if(METHOD == "GET") {				
				$dashbar = new Template(INCLUDES["TPL"]."/dashbar.tpl");
				$dashbar->host = HOST;
				$dashbar->adminuri = $site->adminURI;
				echo $dashbar;
			}

			if(file_exists($file = INCLUDES["VIEWS"].$path."/index.php")) include $file;
			else if(file_exists($file = INCLUDES["VIEWS"].$path.'.php')) include $file;
			else echo new Template(INCLUDES["TPL"]."/errors/404.tpl");
		}

		if($page->theme->hasType("admin")) $page->theme->setType("admin", true);
		else $page->theme->setType("page", true);
		$content = new Template(INCLUDES["TPL"]."/admin.tpl");
		$content->content = trim(ob_get_clean());

		$page->theme->title = $title ?? "Phroses System Page";
		$page->theme->main = (string) $content;
		$page->display();

	}
	
	public function rules(&$page, &$site, &$cascade) {
		return [
			1 => function() use (&$site) { 
				return (
					PATH != "/" &&
					stringStartsWith(PATH, $site->adminURI) && (

						file_exists(($adminpath = INCLUDES["VIEWS"].substr(PATH, strlen($site->adminURI))).".php") || // views/page.php
						file_exists($adminpath) || // views/page.css
						file_exists("$adminpath/index.php") // views/page/index.php
					)
				); 
			}
		];
	}
});

/**
 * GET PAGE/404
 * Displays a a 404 not found error when a page or asset is not found.
 */
self::addRoute(new class extends Route {
	public $method = "get";
	public $response = Phroses::RESPONSES["PAGE"][404];
	
	public function follow(&$page, &$site, &$out) {		
		$out->setCode(404);
		$out->setContentType(MIME_TYPES["HTML"]);
	
		if($page->theme->hasError("404")) die($page->theme->readError("404"));

		$page->theme->setType("page", true);
		$page->theme->title = "404 Not Found";
		$page->theme->main = (string) new Template(INCLUDES["TPL"]."/errors/404.tpl");

		$page->display();
	}
	
	public function rules(&$page, &$site, &$cascade) {
		return [ 
			0 => function() use (&$page) { return !$page->id; },
			5 => function() use (&$page, &$cascade) { 
				return $cascade->getResult() == Phroses::RESPONSES["PAGE"][200] && !$page->visibility && !$_SESSION; 
			}
		];
	}
});

/**
 * (All) ASSET
 * Serves theme asset files
 */
self::addRoute(new class extends Route {
	public $response = Phroses::RESPONSES["ASSET"];
	
	public function follow(&$page, &$site, &$out) { 
		$page->theme->readAsset(PATH); 
	}
	
	public function rules(&$page, &$site, &$cascade) {
		return [ 
			7 => function() use (&$page, &$cascade) { 
				return (
					in_array($cascade->getResult(), [ Phroses::RESPONSES["MAINTENANCE"], Phroses::RESPONSES["PAGE"][404] ]) && 
					$page->theme->hasAsset(PATH)
				); 
			} 
		];
	}
});

/**
 * (All) API
 * Runs the theme API, if it has one
 */
self::addRoute(new class extends Route {
	public $response = Phroses::RESPONSES["API"];

	public function follow(&$page, &$site, &$out) { 
		$page->theme->runApi(); 
	}
	public function rules(&$page, &$site, &$cascade) {
		return [ 
			3 => function() use (&$page) { 
				return (stringStartsWith(PATH, "/api") && $page->theme->hasApi()); 
			} 
		];
	}
});


/**
 * POST (Default handler)
 * This handles all post requests.  If a page does not exist, this route creates one based on request parameters.
 */
self::addRoute(new class extends Route {
	public $method = "post";
	public $response = Phroses::RESPONSES["DEFAULT"];

	public function follow(&$page, &$site, &$out) {
		$out = new JSONServer();

		// Validation
		mapError("access_denied", !$_SESSION, null, 401);
		mapError("resource_exists", Phroses::$response != Phroses::RESPONSES["PAGE"][404]);

		foreach(["title","type"] as $type) {
			mapError("missing_value", !array_key_exists($type, $_REQUEST), [ "field" => $type ]);
		}

		mapError("bad_value", !$page->theme->hasType($_REQUEST["type"]), [ "field" => "type" ]);

		$id = Page::create(PATH, $_REQUEST["title"], $_REQUEST["type"], $_REQUEST["content"] ?? "{}", $site->id);
		$theme = new Theme($site->theme, $_REQUEST["type"]);

		$out->send([ 
			"type" => "success",
			"id" => $id, 
			"content" => $theme->getBody(),
			"typefields" => $theme->getEditorFields()
		], 200);
	}
});

/**
 * PATCH (Default handler)
 * This handles all put requests.  If a page exists, this route edits it based on request parameters.
 */
self::addRoute(new class extends Route {
	public $method = "patch";
	public $response = Phroses::RESPONSES["DEFAULT"];

	public function follow(&$page, &$site, &$out) {
		$out = new JSONServer();

		// Validation
		mapError("access_denied", !$_SESSION, null, 401);
		mapError("resource_missing", !in_array(Phroses::$response, [ Phroses::RESPONSES["PAGE"][200], Phroses::RESPONSES["PAGE"][301] ]));
		mapError("no_change", allKeysDontExist(["type", "uri", "title", "content", "public"], $_REQUEST));
		mapError("bad_value", !$page->theme->hasType($_REQUEST["type"] ?? $page->type), [ "field" => "type" ]);

		if(isset($_REQUEST["uri"])) {
			$count = DB::query("SELECT COUNT(*) AS `count` FROM `pages` WHERE `siteID`=? AND `uri`=?", [ $site->id, $_REQUEST["uri"]])[0]->count ?? 0;
			mapError("resource_exists", $count > 0);
		}

		// do NOT update the database if the request is to change the page to a redirect and there is no content specifying the destination.
		// if the page is a type redirect and there is no destination, an error will be displayed which we should be trying to avoid
		if(!(safeArrayValEquals($_REQUEST, "type", "redirect") && (!isset($_REQUEST["content"]) || 
			(isset($_REQUEST["content"]) && !isset(json_decode($_REQUEST["content"])->destination))))) {
				
			if(isset($_REQUEST["title"])) $page->title = $_REQUEST["title"];
			if(isset($_REQUEST["uri"])) $page->uri = urldecode($_REQUEST["uri"]);
			if(isset($_REQUEST["public"])) $page->public = $_REQUEST["public"];

			if(isset($_REQUEST["content"])) {
				$page->content = htmlspecialchars_decode($_REQUEST["content"]);
			}

			if(isset($_REQUEST["type"])) {
				$page->type = urldecode($_REQUEST["type"]);
				if($_REQUEST["type"] != "redirect") $page->content = "{}";
			} 
		}

		$output = [ "type" => "success" ];
		if(!isset($_REQUEST["nocontent"])) $output["content"] = $page->theme->getBody();
		if(isset($_REQUEST["type"])) $output["typefields"] = $page->theme->getEditorFields($_REQUEST["type"]);

		// if we are changing to type redirect or the page is a redirect, there is no content
		if($page->type == "redirect" || (isset($_REQUEST["type"]) && $_REQUEST["type"] == "redirect")) unset($output["content"]);
		$out->send($output, 200);
	}
});

/**
 * DELETE (Default Handler)
 * This handles all delete requests. If a page exists, this route deletes it.
 */
self::addRoute(new class extends Route {
	public $method = "delete";
	public $response = Phroses::RESPONSES["DEFAULT"];

	public function follow(&$page, &$site, &$out) {
		$out = new JSONServer();
		
		mapError("access_denied", !$_SESSION, null, 401);
		mapError("resource_missing", !in_array(Phroses::$response, [ Phroses::RESPONSES["PAGE"][200], Phroses::RESPONSES["PAGE"][301] ]));

		$page->delete();
		$out->send(["type" => "success"], 200);
	}
});

/**
 * GET UPLOAD
 * This route serves upload files.
 */
self::addRoute(new class extends Route {
	public $method = "get";
	public $response = Phroses::RESPONSES["UPLOAD"];
	
	public function follow(&$page, &$site, &$out) {
		readfileCached(INCLUDES["UPLOADS"]."/".BASEURL."/".substr(PATH, 8));
	}

	public function rules(&$page, &$site, &$cascade) {
		return [ 
			2 => function() { 
				return (
					stringStartsWith(PATH, "/uploads") && 
					file_exists(INCLUDES["UPLOADS"]."/".BASEURL."/".substr(PATH, 8)) && 
					trim(PATH, "/") != "uploads"
				); 
			}
		];
	}
});

/**
 * (All) MAINTENANCE
 * This route displays maintenance mode if a site is in one.
 */
self::addRoute(new class extends Route {
	public $response = Phroses::RESPONSES["MAINTENANCE"];

	public function follow(&$page, &$site, &$out) {
		$out->setCode(503);
		die(new Template(INCLUDES["TPL"]."/maintenance.tpl"));
	}

	public function rules(&$page, &$site, &$cascade) {
		return [
			6 => function() use (&$site, &$cascade) { 
				return $site->maintenance && !$_SESSION && $cascade->getResult() != Phroses::RESPONSES["SYS"][200]; 
			} 
		];
	}
});


return self::$routes;  // return a list of routes for the listen event