<?php
/**
 * This file sets up phroses' routing / method mapping.  This is included
 * from within the start method of the phroses class, so self here refers to 
 * \Phroses\Phroses
 */

namespace Phroses;

use \reqc;
use \reqc\Output;
use \listen\Events;
use \inix\Config as inix;
use \phyrex\Template;
use const \reqc\{ VARS, MIME_TYPES };

self::route("get", self::RESPONSES["PAGE"][200], function() {
	ob_start("ob_gzhandler");

	$theme = new Theme(SITE["THEME"], SITE["PAGE"]["TYPE"]);
	$theme->title = SITE["PAGE"]["TITLE"];
	echo $theme;

	if(inix::get("mode") == "production") {
		ob_end_flush();
		flush();
	}
});

self::route("get", self::RESPONSES["PAGE"][301], function() {
	ob_start("ob_gzhandler");
	$theme = new Theme(SITE["THEME"], SITE["PAGE"]["TYPE"]);

	if(isset(SITE["PAGE"]["CONTENT"]["destination"])) {
        self::$out->redirect(SITE["PAGE"]["CONTENT"]["destination"]);
        
	} else echo "incomplete redirect"; // todo: add a fixer form here

	echo $theme;
	if(inix::get("mode") == "production") {
		ob_end_flush();
		flush();
	}
});

self::route("get", self::RESPONSES["SYS"][200], function() {
	ob_start("ob_gzhandler");
	$theme = new Theme(SITE["THEME"], SITE["PAGE"]["TYPE"]);

	if(!is_dir(INCLUDES["VIEWS"].reqc\PATH) &&
		file_exists(INCLUDES["VIEWS"].reqc\PATH) &&
		strtolower(reqc\EXTENSION) != "php") {
		ReadfileCached(INCLUDES["VIEWS"].reqc\PATH);

	} else {
		ob_start();
		if(!$_SESSION) {
			$theme->push("stylesheets", [ "src" => "/phr-assets/css/main.css" ]);
			$theme->push("scripts", [ "src" => "/phr-assets/js/main.js", "attrs" => "defer" ]);
			self::$out->setCode(401);
			include INCLUDES["VIEWS"]."/admin/login.php";
		
		} else {
			if(reqc\METHOD == "GET") {
				$theme->push("stylesheets", [ "src" => "/phr-assets/css/main.css" ]);
				$theme->push("scripts", [ "src" => "/phr-assets/js/main.js", "attrs" => "defer" ]);
				
				$dashbar = new Template(INCLUDES["TPL"]."/dashbar.tpl");
				$dashbar->host = reqc\HOST;
				echo $dashbar;
			}
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

	echo $theme;
	if(inix::get("mode") == "production") {
		ob_end_flush();
		flush();
	}
});

self::route("get", self::RESPONSES["PAGE"][404], function() {
	ob_start("ob_gzhandler");
	$theme = new Theme(SITE["THEME"], SITE["PAGE"]["TYPE"]);

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

	echo $theme;
	if(inix::get("mode") == "production") {
		ob_end_flush();
		flush();
	}
});


$api = function() {
	ob_start("ob_gzhandler");
	$theme = new Theme(SITE["THEME"], SITE["PAGE"]["TYPE"]);

	if(!$theme->HasAPI()) {
		self::$out->setCode(404);
		$theme->title = "404 Not Found";
		$theme->main = "<h1>404 Not Found</h1><p>The page you are looking for could not be found.  Please check your spelling and try again.</p>";
	} else {
		$theme->RunAPI();
		die;
	}

	echo $theme;
	if(inix::get("mode") == "production") {
		ob_end_flush();
		flush();
	}
};

self::route("get", self::RESPONSES["API"], $api);
self::route("post", self::RESPONSES["API"], $api);
self::route("put", self::RESPONSES["API"], $api);
self::route("delete", self::RESPONSES["API"], $api);
self::route("patch", self::RESPONSES["API"], $api);


self::route("post", self::RESPONSES["DEFAULT"], function() {
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
});

self::route("patch", self::RESPONSES["DEFAULT"], function() {
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
});


self::route("delete", self::RESPONSES["DEFAULT"], function() {
	self::$out = new reqc\JSON\Server();
	if(!$_SESSION) self::$out->send(["type" => "error", "error" => "access_denied"], 401);
	if(SITE["RESPONSE"] != self::RESPONSES["PAGE"][200] && SITE["RESPONSE"] != self::RESPONSES["PAGE"][301]) self::$out->send([ "type" => "error", "error" => "resource_missing" ], 400);

	DB::Query("DELETE FROM `pages` WHERE `uri`=? AND `siteID`=?", [ reqc\PATH, SITE["ID"] ]);
	self::$out->send(["type" => "success"], 200);
});


return self::$handlers;