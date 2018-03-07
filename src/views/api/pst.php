<?php

use Phroses\Phroses;
use phyrex\Template;
use Phroses\{ DB };
use Phroses\Theme\Theme;
use function Phroses\{ handleMethod };
use const Phroses\{ INCLUDES, SITE };

handleMethod("post", function($out) use (&$site) {
    ob_end_clean();

    $theme = new Theme($site->theme, "page");

    $pst = new Template(INCLUDES["TPL"]."/pst.tpl");
    $pst->uri = $_REQUEST["uri"];

    $info = DB::query("SELECT `title`, `content`, `public`, `type`, `id` FROM `pages` WHERE `uri`=? AND `siteID`=?", [ $_REQUEST["uri"], $site->id ]);
    $page = $info[0] ?? null;

    if(count($info) == 0) {
        $pst->pst_type = "new";
        $pst->fields = "";
        $pst->visibility = "checked";
        $pst->title = "";
        $pst->id = -1;

    } else {
        $pst->pst_type = "existing";
        $pst->id = $page->id;
        $pst->fields = $theme->getEditorFields($page->type, json_decode($page->content, true));
        $pst->title = $page->title;
        $pst->visibility = $page->public ? "checked" : "";
    }

    foreach($theme->getTypes() as $type2) $pst->push("types", ["type" => $type2, "checked" => ($page && $page->type == $type2) ? "selected" : "" ]);
    $out->send(["type" => "success", "content" => (string) $pst ], 200);
});

ob_end_clean();
Phroses::followRoute("GET", Phroses::RESPONSES["PAGE"][404]);