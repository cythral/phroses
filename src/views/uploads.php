<?php

use Phroses\Phroses;
use Phroses\Upload;
use phyrex\Template;
use Phroses\Exceptions\UploadException;
use function Phroses\{ handleMethod, parseSize, mapError };
use const Phroses\{ INCLUDES, SITE };

handleMethod("post", function($out) use ($uploaddir, &$site) {
    
    if($_POST['action'] == "rename") {
        $upload = new Upload($site, $_POST["filename"]);

        try {
            if(!$upload->rename($_POST["to"])) throw new UploadException("failed_rename");
        } catch(UploadException $e) {
            $out->send(["type" => "error", "error" => $e->getMessage() ], 400);
        }
    }

    if($_POST["action"] == "delete") {
        $upload = new Upload($site, $_POST["filename"]);

        try {
            if(!$upload->delete()) throw new UploadException("failed_delete");
        } catch(UploadException $e) {
            $out->send(["type" => "error", "error" => $e->getMessage() ], 400);
        }
    }

    if($_POST["action"] == "new") {
        try {
            Upload::create($site, $_POST["filename"], $_FILES["file"]);
        } catch(UploadException $e) {
            $out->send(["type" => "error", "error" => $e->getMessage()], 400);
        }
    }

    $out->send(["type" => "success"], 200);
});

$uploads = new Template(INCLUDES["TPL"]."/admin/uploads.tpl");
$uploads->maxuplsize = parseSize(ini_get("upload_max_filesize"));
$uploads->maxformsize = parseSize(ini_get("post_max_size"));

foreach($site->uploads as $upload) {
    $uploads->push("files", [ "filename" => $upload->name ]);
}

echo $uploads;