<?php
/**
 * This file sets up phroses' routing / method mapping.  This is included
 * from within the start method of the phroses class, so self here refers to 
 * \Phroses\Phroses
 */

namespace Phroses;

use \reqc;
use \Phroses\DB;
use \reqc\Output;
use \listen\Events;
use \inix\Config as inix;
use \phyrex\Template;
use \reqc\JSON\Server as JSONServer;
use const \reqc\{ VARS, MIME_TYPES, PATH, EXTENSION, METHOD, HOST };

self::route("get", self::RESPONSES["PAGE"][200], function(&$page) {

	if(isset($_GET["mode"]) && $_GET["mode"] == "json") {
		self::$out = new JSONServer();
		self::$out->send($page->getAll(), 200);
	}

	$page->display();
});

self::route("get", self::RESPONSES["PAGE"][301], function(&$page) {

	if(isset($page->content["destination"])) {
        self::$out->redirect($page->content["destination"]);
        
	} else echo "incomplete redirect"; // todo: add a fixer form here

	$page->display();
});

self::route("get", self::RESPONSES["SYS"][200], function(&$page) {

	if(!is_dir(INCLUDES["VIEWS"].PATH) &&
		file_exists(INCLUDES["VIEWS"].PATH) &&
		strtolower(EXTENSION) != "php") {
		ReadfileCached(INCLUDES["VIEWS"].PATH);

	} else {
		ob_start();

		$page->theme->push("stylesheets", [ "src" => "/phr-assets/css/main.css" ]);
		$page->theme->push("stylesheets", [ "src" => "//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" ]);
		$page->theme->push("scripts", [ "src" => "/phr-assets/js/main".(inix::get("mode") == "production" ? ".min" : "").".js", "attrs" => "defer" ]);

		if(!$_SESSION) {
			self::$out->setCode(401);
			include INCLUDES["VIEWS"]."/admin/login.php";
		
		} else {
			if(METHOD == "GET") {				
				$dashbar = new Template(INCLUDES["TPL"]."/dashbar.tpl");
				$dashbar->host = HOST;
				echo $dashbar;
			}

			if(file_exists(INCLUDES["VIEWS"].PATH."/index.php")) include INCLUDES["VIEWS"].PATH."/index.php";
			else if(file_exists(INCLUDES["VIEWS"].PATH.'.php')) include INCLUDES["VIEWS"].PATH.".php";
			else echo "resource not found";
		}

		if($page->theme->HasType("admin")) $page->theme->SetType("admin", true);
		$page->theme->title = $title ?? "Phroses System Page";
		$page->theme->main = '<div class="phroses-container">'.trim(ob_get_clean()).'<input type="hidden" id="phr-admin-page" value="true"></div>';
	}

	$page->display();
});

self::route("get", self::RESPONSES["PAGE"][404], function(&$page) {

	if($page->theme->AssetExists(PATH) && $_SERVER["REQUEST_URI"] != "/") {
		$page->theme->AssetRead(PATH); // Assets

	} else {
		
		self::$out->setCode(404);
		self::$out->setContentType(MIME_TYPES["HTML"]);
		
		if($page->theme->ErrorExists("404")) {
			$page->theme->ErrorRead("404"); 
			die;
		}

		$page->theme->SetType("page", true);
		$page->theme->title = "404 Not Found";
		$page->theme->main = "<h1>404 Not Found</h1><p>The page you are looking for could not be found.  Please check your spelling and try again.</p>";
	}

	$page->display();
});


$api = function(&$page) {

	if(!$page->theme->HasAPI()) {
		self::$out->setCode(404);
		$page->theme->title = "404 Not Found";
		$page->theme->main = "<h1>404 Not Found</h1><p>The page you are looking for could not be found.  Please check your spelling and try again.</p>";
	} else {
		$page->theme->RunAPI();
		die;
	}

	$page->display();
};

self::route("get", self::RESPONSES["API"], $api);
self::route("post", self::RESPONSES["API"], $api);
self::route("put", self::RESPONSES["API"], $api);
self::route("delete", self::RESPONSES["API"], $api);
self::route("patch", self::RESPONSES["API"], $api);


self::route("post", self::RESPONSES["DEFAULT"], function(&$page) {
	self::$out = new JSONServer();

	// Validation
	self::error("access_denied", !$_SESSION, null, 401);
	
	foreach(["title","type"] as $type) {
		self::error("missing_value", !array_key_exists($type, $_REQUEST), [ "field" => $type ]);
	}

	self::error("resource_exists", SITE["RESPONSE"] != self::RESPONSES["PAGE"][404]);
	self::error("bad_value", !$page->theme->HasType($_REQUEST["type"]), [ "field" => "type" ]);

	$id = Page::create(PATH, $_REQUEST["title"], $_REQUEST["type"], $_REQUEST["content"] ?? "{}", SITE["ID"]);
	$theme = new Theme(SITE["THEME"], $_REQUEST["type"]);

	self::$out->send([ 
		"type" => "success",
		"id" => $id, 
		"content" => $theme->GetBody(),
		"typefields" => $theme->getEditorFields()
	], 200);
});

self::route("patch", self::RESPONSES["DEFAULT"], function(&$page) {
	self::$out = new JSONServer();

	// Validation
	self::error("access_denied", !$_SESSION, null, 401);
	self::error("resource_missing", SITE["RESPONSE"] != self::RESPONSES["PAGE"][200] && SITE["RESPONSE"] != self::RESPONSES["PAGE"][301]);
	self::error("no_change", keysDontExist(["type", "uri", "title", "content", "public"], $_REQUEST));
	self::error("bad_value", !$page->theme->HasType($_REQUEST["type"] ?? $page->type), [ "field" => "type" ]);

	if(isset($_REQUEST["uri"])) {
		$count = DB::Query("SELECT COUNT(*) AS `count` FROM `pages` WHERE `siteID`=? AND `uri`=?", [ SITE["ID"], $_REQUEST["uri"]])[0]->count ?? 0;
		self::error("resource_exists", $count > 0);
	}

	// do NOT update the database if the request is to change the page to a redirect and there is no content specifying the destination.
	// if the page is a type redirect and there is no destination, an error will be displayed which we should be trying to avoid
	if(!(isset($_REQUEST["type"]) && $_REQUEST["type"] == "redirect" && (!isset($_REQUEST["content"]) || 
		(isset($_REQUEST["content"]) && !isset(json_decode($_REQUEST["content"])->destination))))) {
			
		if(isset($_REQUEST["title"])) $page->title = $_REQUEST["title"];
		if(isset($_REQUEST["uri"])) $page->uri = urldecode($_REQUEST["uri"]);
		if(isset($_REQUEST["type"])) {

			$page->type = urldecode($_REQUEST["type"]);
			$page->theme = new Theme(SITE["THEME"], $page->type);
			if($_REQUEST["type"] != "redirect") $page->content = "{}";

		} else if(isset($_REQUEST["content"])) $page->content = htmlspecialchars_decode($_REQUEST["content"]);
		if(isset($_REQUEST["public"])) $page->public = $_REQUEST["public"];
	}

	$output = [ "type" => "success" ];
	if(!isset($_REQUEST["nocontent"])) $output["content"] = $page->theme->GetBody();
	if(isset($_REQUEST["type"])) $output["typefields"] = $page->theme->getEditorFields($_REQUEST["type"]);

	// if we are changing to type redirect or the page is a redirect, there is no content
	if(SITE["PAGE"]["TYPE"] == "redirect" || (isset($_REQUEST["type"]) && $_REQUEST["type"] == "redirect")) unset($output["content"]);
	self::$out->send($output, 200);
});


self::route("delete", self::RESPONSES["DEFAULT"], function(&$page) {
	self::$out = new JSONServer();
	
	self::error("access_denied", !$_SESSION, null, 401);
	self::error("resource_missing", SITE["RESPONSE"] != self::RESPONSES["PAGE"][200] && SITE["RESPONSE"] != self::RESPONSES["PAGE"][301]);

	$page->delete();
	self::$out->send(["type" => "success"], 200);
});


self::route("get", self::RESPONSES["UPLOAD"], function() {
	ReadfileCached(INCLUDES["UPLOADS"]."/".reqc\BASEURL."/".substr(PATH, 8));
});

return self::$handlers;