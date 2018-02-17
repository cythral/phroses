<?php

use phyrex\Template;
use Phroses\{ DB, Theme };
use function Phroses\{ handleMethod };
use const Phroses\{ INCLUDES, SITE };

handleMethod("post", function($out) {
    ob_end_clean();

    $theme = new Theme(SITE["THEME"], "page");

    $pst = new Template(INCLUDES["TPL"]."/pst.tpl");
    $pst->uri = $_REQUEST["uri"];

    $info = DB::Query("SELECT `title`, `content`, `public`, `type`, `id` FROM `pages` WHERE `uri`=? AND `siteID`=?", [ $_REQUEST["uri"], SITE["ID"] ]);
    $page = $info[0] ?? null;

    if(count($info) == 0) {
        $pst->pst_type = "new";
        $pst->fields = "";
        $pst->visibility = "checked";
        $pst->title = "";
        $pst->id = -1;

    } else {
        $pst->pst_type = "existing";

        
        $page->content = json_decode($page->content);

        ob_start();
        foreach($theme->getContentFields($page->type) as $key => $field) {
            if($field == "editor") { ?><pre class="form_field content editor" id="<?= $page->type; ?>-main" data-id="<?= $key; ?>"><?= trim(htmlspecialchars($page->content->{$key} ?? "")); ?></pre><? }
            else if(in_array($field, ["text", "url"])) { ?><input id="<?= $key; ?>" placeholder="<?= $key; ?>" type="<?= $field; ?>" class="form_input form_field content" value="<?= htmlspecialchars($page->content->{$key} ?? ""); ?>"><? }
        }

        $pst->id = $page->id;
        $pst->fields = trim(ob_get_clean());
        $pst->title = $page->title;
        $pst->visibility = $page->public ? "checked" : "";
    }

    foreach($theme->getTypes() as $type2) $pst->push("types", ["type" => $type2, "checked" => ($page && $page->type == $type2) ? "selected" : "" ]);
    $out->send(["type" => "success", "content" => (String)$pst ], 200);
});