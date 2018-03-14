<?php

use Phroses\Phroses;
use Phroses\Upload;
use phyrex\Template;
use \Phroses\Switcher;
use \Phroses\Switches\MethodSwitch;
use \Phroses\Exceptions\UploadException;
use \reqc\JSON\Server as JsonServer;
use function Phroses\{ handleMethod, parseSize, mapError };
use const Phroses\{ INCLUDES, SITE };

(new MethodSwitch(null, [ $site ]))

->case("get", function($out, $site) {
    $uploads = new Template(INCLUDES["TPL"]."/admin/uploads.tpl");
    $uploads->maxuplsize = parseSize(ini_get("upload_max_filesize"));
    $uploads->maxformsize = parseSize(ini_get("post_max_size"));

    foreach($site->uploads as $upload) {
        $uploads->push("files", [ "filename" => $upload->name ]);
    }

    echo $uploads;
});
