<?php

use phyrex\Template;
use \Phroses\{ DB, Theme };
use function Phroses\{ HandleMethod };
use const \Phroses\{ SITE, INCLUDES };

handleMethod("POST", function($out) {
    if(!empty($_POST["theme"])) {
        mapError("bad_theme", !file_exists(INCLUDES["THEMES"]."/".$_POST["theme"]));
        DB::Query("UPDATE `sites` SET `theme`=? WHERE `id`=?", [ $_POST["theme"], SITE["ID"] ]);
    }

    if(!empty($_POST["uri"])) {
        $_POST["uri"] = "/".trim($_POST["uri"], "/");

        $count = DB::Query("SELECT COUNT(*) AS `count` FROM `pages` WHERE `uri`=? AND `siteID`=?", [ $_POST["uri"], SITE["ID"] ])[0]->count ?? 0;
        mapError("resource_exists", $count != 0);
        mapError("bad_uri", $_POST['uri'] == "/");

        DB::Query("UPDATE `sites` SET `adminURI`=? WHERE `id`=?", [ $_POST["uri"], SITE["ID"] ]);
    }

    if(isset($_POST["maintenance"])) {
        DB::Query("UPDATE `sites` SET `maintenance`=? WHERE `id`=?", [ (bool)$_POST["maintenance"], SITE["ID"] ]);
    }

    $out->send(["type" => "success"], 200);
});


$vars = DB::Query("SELECT SUM(`views`) AS viewcount, COUNT(`id`) AS pagecount FROM `pages` WHERE `siteID`=?", [ SITE["ID"] ])[0];

$index = new Template(INCLUDES["TPL"]."/admin/index.tpl");
$index->pagecount = ($vars->pagecount > 999) ? "999+" : $vars->pagecount;
$index->fullpagecount = $vars->pagecount;
$index->viewcount = (($views = ($vars->viewcount ?? 0)) > 999) ? "999+" : $views;
$index->fullviewcount = $vars->viewcount;
$index->adminuri = SITE["ADMINURI"];

foreach(Theme::List() as $thm) {
    $index->push("themes", [ "name" => $thm, "selected" => ($thm == SITE["THEME"]) ? "selected" : "" ]);
}

foreach([[1,"on"], [0, "off"]] as list($value, $name)) {
    $index->push("moption", [ "value" => $value, "name" => $name, "selected" => (bool)$value == SITE["MAINTENANCE"] ? "selected" : "" ]);
}

echo $index;