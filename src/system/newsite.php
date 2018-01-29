<?php

use \Phroses\DB;
use \phyrex\Template;
use \inix\Config as inix;
use function \Phroses\handleMethod;
use const \Phroses\{ SRC, INCLUDES };
use const \reqc\{ BASEURL };

handleMethod("post", function($out) {
  
  if(strlen($_POST["password"]) > 50) $out->send(["error" => "pw_length" ], 400);

  DB::Query("INSERT INTO `sites` (`url`, `theme`, `name`,`adminUsername`, `adminPassword`) VALUES (?, 'bloom', ?, ?, ?)", [
    BASEURL,
    $_POST["name"],
    $_POST["username"],
    password_hash(inix::get("pepper").$_POST["password"], PASSWORD_DEFAULT)
  ]);
  
  $out->send(["type" => "success"], 200);

}, ["name", "username", "password"]);

self::$out->setCode(404);
$newsite = new Template(INCLUDES["TPL"]."/newsite.tpl");
$newsite->url = BASEURL;
$newsite->styles = file_get_contents(SRC."/views/phr-assets/css/main.css");
$newsite->script = file_get_contents(SRC."/views/phr-assets/js/install.js");
die($newsite);