<?php

use Phroses\Phroses;
use phyrex\Template;
use inix\Config as inix;
use Phroses\DB;
use \reqc\JSON\Server as JsonServer;
use Phroses\Switches\MethodSwitch;
use function \Phroses\{ handleMethod };
use const \Phroses\{ SITE, INCLUDES };

if($_SESSION) $out->redirect("/admin");

(new MethodSwitch)

->case("post", function($out, &$site) {

    if(!$site->login($_POST["username"], $_POST["password"])) {
        $out->send(["type" => "error"], 401);
    }

    $_SESSION["live"] = "true";
    $out->send(["type" => "success"], 200);

}, [ $site ], JsonServer::class)

->case("get", function($out) {

    echo new Template(INCLUDES["TPL"]."/admin/login.tpl");
    
});


