<?php

use Phroses\Phroses;
use phyrex\Template;
use inix\Config as inix;
use Phroses\DB;
use function \Phroses\{ handleMethod };
use const \Phroses\{ SITE, INCLUDES };

if($_SESSION) $out->redirect("/admin");

handleMethod("POST", function($out) use (&$site) {
    // generate pepper if its not there
    if(inix::get("pepper") == null) {
        inix::set("pepper", bin2hex(openssl_random_pseudo_bytes(10)));
    }

    if(password_verify($_POST["password"], $site->adminPassword)) {
        DB::Query("UPDATE `sites` SET `adminPassword`=? WHERE `id`=?", [ password_hash(inix::get("pepper").$_POST["password"], PASSWORD_DEFAULT), $site->id ]);
        $_SESSION["live"] = "true";
        $out->send(["type" => "success"], 200);

    } else if(password_verify(inix::get("pepper").$_POST["password"], $site->adminPassword) && $_POST["username"] == $site->adminUsername) {
        $_SESSION["live"] = "true";
        $out->send(["type" => "success"], 200);
    }

    $out->send(["type" => "error"], 401);

});


echo new Template(INCLUDES["TPL"]."/admin/login.tpl");
