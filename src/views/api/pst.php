<?php

use Phroses\Phroses;
use phyrex\Template;
use Phroses\{ DB };
use Phroses\Theme\Theme;
use \Phroses\Switches\MethodSwitch;
use \reqc\JSON\Server as JsonServer;
use \Phroses\Routes\Controller as RouteController;
use function Phroses\{ handleMethod };
use const Phroses\{ INCLUDES, SITE };

(new MethodSwitch(null, [ $site ]))

->case("post", function($out, &$site) {
    ob_end_clean();
    
    $page = $site->getPage($_REQUEST["uri"]);
    $theme = new Theme($site->theme, "page");

    $pst = new Template(INCLUDES["TPL"]."/pst.tpl");
    $pst->uri = $_REQUEST["uri"];
    
    if(!$page) {
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

    foreach($theme->getTypes() as $type) $pst->push("types", ["type" => $type, "checked" => ($page && $page->type == $type) ? "selected" : "" ]);
    $out->send(["type" => "success", "content" => (string) $pst ], 200);
    
}, [], JsonServer::class)

->case("get", (function($out, $page, $site) {
    ob_end_clean();
    
    // reroute to 404
    $this->controller
        ->select(\reqc\METHOD, RouteController::RESPONSES["PAGE"]["404"])
        ->follow($page, $site, $out);

})->bindTo($this), [ $page, $site ]);

