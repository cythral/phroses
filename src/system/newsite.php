<?php

use \Phroses\Site;
use \phyrex\Template;
use \inix\Config as inix;
use function \Phroses\{ handleMethod, mapError };
use const \Phroses\{ SRC, INCLUDES };
use const \reqc\{ BASEURL };

handleMethod("post", function($out) {

    mapError("pw_length", strlen($_POST["password"]) > 50);
    mapError("create_fail", !Site::create($_POST["name"], BASEURL, 'bloom', '/admin', $_POST["username"], $_POST["password"]));
    
    $out->send(["type" => "success"], 200);

}, ["name", "username", "password"]);

self::$out->setCode(404);
$newsite = new Template(INCLUDES["TPL"]."/newsite.tpl");
$newsite->url = BASEURL;
$newsite->styles = file_get_contents(SRC."/views/assets/css/main.css");
$newsite->script = file_get_contents(SRC."/views/assets/js/install.js");

die($newsite);