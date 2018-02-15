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

use const \reqc\{ VARS, MIME_TYPES, PATH, EXTENSION, METHOD, HOST, BASEURL };

self::addRoute("get", self::RESPONSES["PAGE"][200], function(&$page) {

	if(arrayValEquals($_GET, "mode", "json")) {
		self::$out = new JSONServer();
		self::$out->send($page->getAll(), 200);
	}

	$page->display();
});

self::addRoute("get", self::RESPONSES["PAGE"][301], function(&$page) {

	if(array_key_exists("destination", $page->content) && !empty($page->content["destination"]) && $page->content["destination"] != PATH) {
        self::$out->redirect($page->content["destination"]);
	} 
	
	$page->theme->setType("page", true);
	$page->display([ "main" => (string) new Template(INCLUDES["TPL"]."/errors/redirect.tpl") ]);
});

self::addRoute("get", self::RESPONSES["SYS"][200], function(&$page) {
	$path = substr(PATH, strlen(SITE["ADMINURI"]));

	if(!is_dir($file = INCLUDES["VIEWS"].$path) && file_exists($file) && strtolower(EXTENSION) != "php") {
		readfileCached($file);
	}

	ob_start();
	$page->theme->push("stylesheets", [ "src" => SITE["ADMINURI"]."/assets/css/main.css" ]);
	$page->theme->push("stylesheets", [ "src" => "//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" ]);
	$page->theme->push("scripts", [ "src" => SITE["ADMINURI"]."/assets/js/main".(inix::get("mode") == "production" ? ".min" : "").".js", "attrs" => "defer" ]);

	if(!$_SESSION) {
		self::$out->setCode(401);
		include INCLUDES["VIEWS"]."/login.php";
	
	} else {
		if(METHOD == "GET") {				
			$dashbar = new Template(INCLUDES["TPL"]."/dashbar.tpl");
			$dashbar->host = HOST;
			$dashbar->adminuri = SITE["ADMINURI"];
			echo $dashbar;
		}

		if(file_exists($file = INCLUDES["VIEWS"].$path."/index.php")) include $file;
		else if(file_exists($file = INCLUDES["VIEWS"].$path.'.php')) include $file;
		else echo new Template(INCLUDES["TPL"]."/errors/404.tpl");
	}

	if($page->theme->hasType("admin")) $page->theme->setType("admin", true);
	$content = new Template(INCLUDES["TPL"]."/admin.tpl");
	$content->content = trim(ob_get_clean());

	$page->theme->title = $title ?? "Phroses System Page";
	$page->theme->main = (string) $content;

	$page->display();
});

self::addRoute("get", self::RESPONSES["PAGE"][404], function(&$page) {		
	self::$out->setCode(404);
	self::$out->setContentType(MIME_TYPES["HTML"]);
	
	if($page->theme->errorExists("404")) die($page->theme->errorRead("404"));

	$page->theme->setType("page", true);
	$page->theme->title = "404 Not Found";
	$page->theme->main = (string) new Template(INCLUDES["TPL"]."/errors/404.tpl");

	$page->display();
});

self::addRoute(null, self::RESPONSES["ASSET"], function(&$page) { $page->theme->assetRead(PATH); });
self::addRoute(null, self::RESPONSES["API"], function(&$page) { $page->theme->runAPI(); });

self::addRoute("post", self::RESPONSES["DEFAULT"], function(&$page) {
	self::$out = new JSONServer();

	// Validation
	mapError("access_denied", !$_SESSION, null, 401);
	mapError("resource_exists", self::$response != self::RESPONSES["PAGE"][404]);

	foreach(["title","type"] as $type) {
		mapError("missing_value", !array_key_exists($type, $_REQUEST), [ "field" => $type ]);
	}

	mapError("bad_value", !$page->theme->hasType($_REQUEST["type"]), [ "field" => "type" ]);

	$id = Page::create(PATH, $_REQUEST["title"], $_REQUEST["type"], $_REQUEST["content"] ?? "{}", SITE["ID"]);
	$theme = new Theme(SITE["THEME"], $_REQUEST["type"]);

	self::$out->send([ 
		"type" => "success",
		"id" => $id, 
		"content" => $theme->getBody(),
		"typefields" => $theme->getEditorFields()
	], 200);
});

self::addRoute("patch", self::RESPONSES["DEFAULT"], function(&$page) {
	self::$out = new JSONServer();

	// Validation
	mapError("access_denied", !$_SESSION, null, 401);
	mapError("resource_missing", !in_array(SITE["RESPONSE"], [ self::RESPONSES["PAGE"][200], self::RESPONSES["PAGE"][301] ]));
	mapError("no_change", keysDontExist(["type", "uri", "title", "content", "public"], $_REQUEST));
	mapError("bad_value", !$page->theme->hasType($_REQUEST["type"] ?? $page->type), [ "field" => "type" ]);

	if(isset($_REQUEST["uri"])) {
		$count = DB::Query("SELECT COUNT(*) AS `count` FROM `pages` WHERE `siteID`=? AND `uri`=?", [ SITE["ID"], $_REQUEST["uri"]])[0]->count ?? 0;
		mapError("resource_exists", $count > 0);
	}

	// do NOT update the database if the request is to change the page to a redirect and there is no content specifying the destination.
	// if the page is a type redirect and there is no destination, an error will be displayed which we should be trying to avoid
	if(!(arrayValEquals($_REQUEST, "type", "redirect") && (!isset($_REQUEST["content"]) || 
		(isset($_REQUEST["content"]) && !isset(json_decode($_REQUEST["content"])->destination))))) {
			
		if(isset($_REQUEST["title"])) $page->title = $_REQUEST["title"];
		if(isset($_REQUEST["uri"])) $page->uri = urldecode($_REQUEST["uri"]);
		if(isset($_REQUEST["public"])) $page->public = $_REQUEST["public"];
		if(isset($_REQUEST["content"])) $page->content = htmlspecialchars_decode($_REQUEST["content"]);
		if(isset($_REQUEST["type"])) {
			$page->type = urldecode($_REQUEST["type"]);
			$page->theme = new Theme(SITE["THEME"], $page->type);
			if($_REQUEST["type"] != "redirect") $page->content = "{}";
		} 
	}

	$output = [ "type" => "success" ];
	if(!isset($_REQUEST["nocontent"])) $output["content"] = $page->theme->getBody();
	if(isset($_REQUEST["type"])) $output["typefields"] = $page->theme->getEditorFields($_REQUEST["type"]);

	// if we are changing to type redirect or the page is a redirect, there is no content
	if(SITE["PAGE"]["TYPE"] == "redirect" || (isset($_REQUEST["type"]) && $_REQUEST["type"] == "redirect")) unset($output["content"]);
	self::$out->send($output, 200);
});


self::addRoute("delete", self::RESPONSES["DEFAULT"], function(&$page) {
	self::$out = new JSONServer();
	 
	mapError("access_denied", !$_SESSION, null, 401);
	mapError("resource_missing", !in_array(Phroses::$response, [ self::RESPONSES["PAGE"][200], self::RESPONSES["PAGE"][301] ]));

	$page->delete();
	self::$out->send(["type" => "success"], 200);
});


self::addRoute("get", self::RESPONSES["UPLOAD"], function() {
	readfileCached(INCLUDES["UPLOADS"]."/".BASEURL."/".substr(PATH, 8));
});

self::addRoute(null, self::RESPONSES["MAINTENANCE"], function(&$page) {
	self::$out->setCode(503);
	die(new Template(INCLUDES["TPL"]."/maintenance.tpl"));
});

return self::$routes;