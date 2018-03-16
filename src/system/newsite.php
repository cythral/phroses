<?php

use \Phroses\Switches\MethodSwitch;
use \Phroses\Site;
use \phyrex\Template;
use \inix\Config as inix;
use \Phroses\JsonServer;
use function \Phroses\{ handleMethod, mapError };
use const \Phroses\{ SRC, INCLUDES };
use const \reqc\{ BASEURL };

(new MethodSwitch)
->case("post", function($out) {

    $out->error("pw_length", strlen($_POST["password"]) > 50);
    $out->error("create_fail", !Site::create($_POST["name"], BASEURL, 'bloom', '/admin', $_POST["username"], $_POST["password"]));
    $out->success();

}, [], JsonServer::class)

->case("get", function($out) {

    $newsite = new Template(INCLUDES["TPL"]."/newsite.tpl");
    $newsite->url = BASEURL;
    $newsite->styles = file_get_contents(SRC."/views/assets/css/phroses.css");
    $newsite->script = file_get_contents(SRC."/views/assets/js/phroses.min.js");
    echo $newsite;

});
