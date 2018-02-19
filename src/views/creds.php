<?php

use phyrex\Template;
use Phroses\DB;
use inix\Config as inix;
use function Phroses\{ handleMethod };
use const Phroses\{ SITE, INCLUDES };

handleMethod("post", function($out) {
    
    mapError("bad_value", empty($_POST["username"]), [ "field" => "username" ]);

    if($_POST["old"] != "" || $_POST["new"] != "" || $_POST["repeat"] != "") {
        mapError("bad_value", !password_verify(inix::get("pepper").$_POST["old"], SITE["PASSWORD"]), [ "field" => "old" ]);
        mapError("pw_length", strlen($_POST["new"]) > 50);
        mapError("bad_value", $_POST["new"] != $_POST["repeat"], [ "field" => "repeat" ]);

        DB::Query("UPDATE `sites` SET `adminPassword`=? WHERE `id`=?", [
            password_hash(inix::get("pepper").$_POST["new"], PASSWORD_DEFAULT),
            SITE["ID"]
        ]);
    }

    DB::Query("UPDATE `sites` SET `adminUsername`=? WHERE `id`=?", [
        $_POST["username"],
        SITE["ID"]
    ]);

    $out->send(["type" => "success"], 200);

}, ["username", "old", "new", "repeat"]);


$creds = new Template(INCLUDES["TPL"]."/admin/creds.tpl");
$creds->username = SITE["USERNAME"];
echo $creds;