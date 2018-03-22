<?php

use \phyrex\Template;
use \Phroses\Switches\MethodSwitch;
use const \Phroses\{ VERSION, INCLUDES };

(new MethodSwitch)

->case("get", function() {
    $version = file_get_contents("https://api.phroses.com/version-stable");

    if(VERSION == "{{version}}") {

        $tpl = new Template(INCLUDES["TPL"]."/admin/update/uptodate.tpl");

    } else if(version_compare(VERSION, $version, "<")) {

        $tpl = new Template(INCLUDES["TPL"]."/admin/update/available.tpl");

    } else {

        $tpl = new Template(INCLUDES["TPL"]."/admin/update/uptodate.tpl");

    }

    $tpl->version = $version;
    echo $tpl;
});

