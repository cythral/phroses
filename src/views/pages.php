<?php

use phyrex\Template;
use Phroses\DB;
use const Phroses\{ SITE, INCLUDES };
use const reqc\{ BASEURL };

$q = DB::Query("SELECT * FROM `pages` WHERE `siteID`=?", [ SITE["ID"] ]);

$pages = new Template(INCLUDES["TPL"]."/admin/pages.tpl");

if(count($q) == 0) $pages->empty = "<em>No pages for ".BASEURL."</em>";

foreach($q as $p) {
    ob_start();
    
    foreach($page->theme->GetTypes() as $type) {
        ?><option <?= ($type == "redirect") ? "disabled" : ""; ?> <?= ($type == $p->type) ? "selected" : ""; ?>><?= $type; ?></option><?
    } 
    
    $pages->push("pages", [ 
        "uri" => $p->uri, 
        "id" => $p->id, 
        "title" => $p->title,
        "types" => trim(ob_get_clean())
    ]);
}

echo $pages;