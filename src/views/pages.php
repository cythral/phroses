<?php

use Phroses\Phroses;
use phyrex\Template;
use Phroses\DB;
use const Phroses\{ SITE, INCLUDES };
use const reqc\{ BASEURL };

$pageslist = $site->pages;

$pagesview = new Template(INCLUDES["TPL"]."/admin/pages.tpl");

if(count($pageslist) == 0) {
    $pagesview->empty = "<em>No pages for ".BASEURL."</em>";
}

foreach($pageslist as $item) {
    ob_start();
    
    foreach($page->theme->GetTypes() as $type) {
        ?><option <?= ($type == "redirect") ? "disabled" : ""; ?> <?= ($type == $item->type) ? "selected" : ""; ?>><?= $type; ?></option><?php
    } 
    
    $pagesview->push("pages", [ 
        "uri" => $item->uri, 
        "id" => $item->id, 
        "title" => $item->title,
        "types" => trim(ob_get_clean())
    ]);
}

echo $pagesview;