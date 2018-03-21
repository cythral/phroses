<?php

use \Phroses\Switches\MethodSwitch;
use \phyrex\Template;

use const \Phroses\{ INCLUDES, VERSION, BUILD_TIMESTAMP };

(new MethodSwitch)

->case("get", function() {
    $info = new Template(INCLUDES["TPL"]."/admin/info.tpl");
    $version = VERSION;
    if(defined("Phroses\BUILD_TIMESTAMP")) $version .= " (built: ".date('F j, Y @ H:i:s e', BUILD_TIMESTAMP).")";

    $info->version = $version;
    echo $info;
});