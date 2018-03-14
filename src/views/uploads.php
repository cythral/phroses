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

->case("post", function($out, $site) {
    try {

        (new Switcher(strtolower($_POST["action"]), [ new Upload($site, $_POST["filename"]) ]))

        ->case("rename", function($upload) {
            if(!$upload->rename($_POST["to"])) throw new UploadException("failed_rename");
        })

        ->case("delete", function(&$upload) {
            if(!$upload->delete()) throw new UploadException("failed_delete");
        })

        ->case("new", function($site) {
            Upload::create($site, $_POST["filename"], $_FILES["file"]);
        }, [ $site ]);

    } catch(UploadException $e) {
        $out->send(["type" => "error", "error" => $e->getMessage() ], 400);
    }

    $out->send(["type" => "success"], 200);

}, [], JsonServer::class)

->case("get", function($out, $site) {
    $uploads = new Template(INCLUDES["TPL"]."/admin/uploads.tpl");
    $uploads->maxuplsize = parseSize(ini_get("upload_max_filesize"));
    $uploads->maxformsize = parseSize(ini_get("post_max_size"));

    foreach($site->uploads as $upload) {
        $uploads->push("files", [ "filename" => $upload->name ]);
    }

    echo $uploads;
});
