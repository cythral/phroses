<?php

use \Phroses\DB;
use \phyrex\Template;
use function \Phroses\handleMethod;
use const \Phroses\{ SRC, INCLUDES };
use const \reqc\{ BASEURL };

handleMethod("post", function($out) {
  
  DB::Query("INSERT INTO `sites` (`url`, `theme`, `name`,`adminUsername`, `adminPassword`) VALUES (?, 'bloom', ?, ?, ?)", [
    BASEURL,
    $_POST["name"],
    $_POST["username"],
    password_hash($_POST["password"], PASSWORD_DEFAULT)
  ]);
  
  $out->send(["type" => "success"], 200);

}, ["name", "username", "password"]);

self::$out->setCode(404);
$newsite = new Template(INCLUDES["TPL"]."/newsite.tpl");
$newsite->url = BASEURL;
$newsite->styles = file_get_contents(SRC."/views/phr-assets/css/main.css");
$newsite->script = file_get_contents(SRC."/views/phr-assets/js/install.js");
die($newsite);