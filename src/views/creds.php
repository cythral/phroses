<?php

use \Phroses\Phroses;
use \Phroses\Switches\MethodSwitch;
use \phyrex\Template;
use \Phroses\DB;
use \reqc\JSON\Server as JsonServer;
use \inix\Config as inix;
use function \Phroses\{ handleMethod, mapError };
use const \Phroses\{ SITE, INCLUDES };

(new MethodSwitch(null, [ $site ]))

->case("post", function($out, $site) {

    mapError("bad_value", empty($_POST["username"]), [ "field" => "username" ]);

    if($_POST["old"] != "" || $_POST["new"] != "" || $_POST["repeat"] != "") {
        mapError("bad_value", !password_verify(inix::get("pepper").$_POST["old"], $site->adminPassword), [ "field" => "old" ]);
        mapError("pw_length", strlen($_POST["new"]) > 50);
        mapError("bad_value", $_POST["new"] != $_POST["repeat"], [ "field" => "repeat" ]);

        $site->adminPassword = $_POST["new"];
    }

    $site->adminUsername = $_POST["username"];
    $out->send(["type" => "success"], 200);

}, JsonServer::class)

->case("get", function($out, $site) {

    $creds = new Template(INCLUDES["TPL"]."/admin/creds.tpl");
    $creds->username = $site->adminUsername;
    echo $creds;

});



