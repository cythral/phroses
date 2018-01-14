<?php

use phyrex\Template;
use Phroses\DB;
use const Phroses\{ SITE, INCLUDES };
use const reqc\{ BASEURL };

$q = DB::Query("SELECT * FROM `pages` WHERE `siteID`=?", [ SITE["ID"] ]);

$pages = new Template(INCLUDES["TPL"]."/admin/pages.tpl");

if(count($q) == 0) $pages->empty = "<em>No pages for ".BASEURL."</em>";

foreach($q as $page) {
    ob_start();
    foreach($theme->GetTypes() as $type) {
        ?><option <?= ($type == "redirect") ? "disabled" : ""; ?> <?= ($type == $page->type) ? "selected" : ""; ?>><?= $type; ?></option><?
    } 
    
    $pages->push("pages", [ 
        "uri" => $page->uri, 
        "id" => $page->id, 
        "title" => $page->title,
        "types" => ob_get_clean()
    ]);
}

echo $pages;