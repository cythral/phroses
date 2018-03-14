<?php

use \Phroses\Phroses;
use \Phroses\Switches\MethodSwitch;
use phyrex\Template;
use \Phroses\Theme\Theme;
use \reqc\JSON\Server as JsonServer;
use function Phroses\{ mapError };
use const \Phroses\{ INCLUDES };
use const \reqc\{ HOST, METHOD };



(new MethodSwitch(null, [ $site ]))

->case("post", function($out, $site) {

    if(!empty($_POST["theme"])) {
        mapError("bad_theme", !file_exists(INCLUDES["THEMES"]."/".$_POST["theme"]));
        $site->theme = $_POST["theme"];
    }

    if(!empty($_POST["uri"])) {
        $_POST["uri"] = "/".trim($_POST["uri"], "/");

        mapError("resource_exists", $site->hasPage($_POST["uri"]));
        mapError("bad_uri", $_POST['uri'] == "/");

        $site->adminURI = $_POST["uri"];
    }

    if(isset($_POST["maintenance"])) $site->maintenance = (bool) $_POST["maintenance"];    
    if(!empty($_POST["name"])) $site->name = $_POST["name"];
    if(!empty($_POST["url"])) $site->url = $_POST["url"];

    $out->send(["type" => "success"], 200);

}, [], JsonServer::class)

->case("get", function($out, $site) {

    $index = new Template(INCLUDES["TPL"]."/admin/index.tpl");
    $index->pagecount = ($site->pageCount > 999) ? "999+" : $site->pageCount;
    $index->fullpagecount = $site->pageCount;
    $index->viewcount = ($site->views > 999) ? "999+" : $site->views;
    $index->fullviewcount = $site->views;
    $index->adminuri = $site->adminURI;
    $index->host = HOST;
    
    foreach(Theme::list() as $thm) {
        $index->push("themes", [ "name" => $thm, "selected" => ($thm == $site->theme) ? "selected" : "" ]);
    }
    
    foreach([[1,"on"], [0, "off"]] as list($value, $name)) {
        $index->push("moption", [ "value" => $value, "name" => $name, "selected" => (bool)$value == $site->maintenance ? "selected" : "" ]);
    }
    
    echo $index;

});

