<?

use phyrex\Template;
use \Phroses\{ DB, Theme };
use function Phroses\{ HandleMethod };
use const \Phroses\{ SITE, INCLUDES };

handleMethod("POST", function($out) {
    if(!file_exists(INCLUDES["THEMES"]."/".$_POST["theme"])) $out->send(["type" => "error", "error" => "bad_theme"], 400);
    DB::Query("UPDATE `sites` SET `theme`=? WHERE `id`=?", [ $_POST["theme"], SITE["ID"] ]);
    $out->send(["type" => "success"], 200);
});


$vars = DB::Query("SELECT SUM(`views`) AS viewcount, COUNT(`id`) AS pagecount FROM `pages` WHERE `siteID`=?", [ SITE["ID"] ])[0];

$index = new Template(INCLUDES["TPL"]."/admin/index.tpl");
$index->pagecount = $vars->pagecount;
$index->viewcount = $vars->viewcount ?? 0;

foreach(Theme::List() as $thm) {
    $index->push("themes", [ "name" => $thm, "selected" => ($thm == SITE["THEME"]) ? "selected" : "" ]);
}

echo $index;