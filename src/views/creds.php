<?php

use \Phroses\Phroses;
use \Phroses\Switches\MethodSwitch;
use \phyrex\Template;
use \Phroses\JsonServer as JsonServer;
use \inix\Config as inix;
use function \Phroses\{ handleMethod, mapError };
use const \Phroses\{ SITE, INCLUDES };

(new MethodSwitch(null, [ $site ]))

->case("post", function($out, $site) {

    $out->error("bad_value", empty($_POST["username"]), 400, [ "field" => "username" ]);

    if($_POST["old"] != "" || $_POST["new"] != "" || $_POST["repeat"] != "") {
        $out->error("bad_value", !password_verify(inix::get("pepper").$_POST["old"], $site->adminPassword), 400, [ "field" => "old" ]);
        $out->error("pw_length", strlen($_POST["new"]) > 50);
        $out->error("bad_value", $_POST["new"] != $_POST["repeat"], 400, [ "field" => "repeat" ]);

        $site->adminPassword = $_POST["new"];
    }

    $site->adminUsername = $_POST["username"];
    $out->success();

}, [], JsonServer::class)

->case("get", function($out, $site) {

    $creds = new Template(INCLUDES["TPL"]."/admin/creds.tpl");
    $creds->username = $site->adminUsername;
    echo $creds;

});



