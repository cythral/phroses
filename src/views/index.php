<?php

use \Phroses\Phroses;
use phyrex\Template;
use \Phroses\{ DB, Theme\Theme };
use function Phroses\{ HandleMethod, mapError };
use const \Phroses\{ SITE, INCLUDES };
use const \reqc\{ HOST };

handleMethod("POST", function($out) use (&$site) {
    if(!empty($_POST["theme"])) {
        mapError("bad_theme", !file_exists(INCLUDES["THEMES"]."/".$_POST["theme"]));
        $site->theme = $_POST["theme"];
    }

    if(!empty($_POST["uri"])) {
        $_POST["uri"] = "/".trim($_POST["uri"], "/");

        $count = DB::query("SELECT COUNT(*) AS `count` FROM `pages` WHERE `uri`=? AND `siteID`=?", [ $_POST["uri"], $site->id ])[0]->count ?? 0;
        mapError("resource_exists", $count != 0);
        mapError("bad_uri", $_POST['uri'] == "/");

        $site->adminURI = $_POST["uri"];
    }

    if(isset($_POST["maintenance"])) $site->maintenance = (bool)$_POST["maintenance"];    
    if(!empty($_POST["name"])) $site->name = $_POST["name"];
    if(!empty($_POST["url"])) $site->url = $_POST["url"];

    $out->send(["type" => "success"], 200);
});


$vars = DB::query("SELECT SUM(`views`) AS viewcount, COUNT(`id`) AS pagecount FROM `pages` WHERE `siteID`=?", [ $site->id ])[0];

$index = new Template(INCLUDES["TPL"]."/admin/index.tpl");
$index->pagecount = ($vars->pagecount > 999) ? "999+" : $vars->pagecount;
$index->fullpagecount = $vars->pagecount;
$index->viewcount = (($views = ($vars->viewcount ?? 0)) > 999) ? "999+" : $views;
$index->fullviewcount = $vars->viewcount;
$index->adminuri = $site->adminURI;
$index->host = HOST;

foreach(Theme::list() as $thm) {
    $index->push("themes", [ "name" => $thm, "selected" => ($thm == $site->theme) ? "selected" : "" ]);
}

foreach([[1,"on"], [0, "off"]] as list($value, $name)) {
    $index->push("moption", [ "value" => $value, "name" => $name, "selected" => (bool)$value == $site->maintenance ? "selected" : "" ]);
}

echo $index;