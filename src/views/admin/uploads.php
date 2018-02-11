<?php

use phyrex\Template;
use function Phroses\{ handleMethod };
use const Phroses\{ INCLUDES };

$uploaddir = INCLUDES["UPLOADS"]."/".reqc\BASEURL."/";

function parseSize($size) {
    $unit = strtolower(substr($size, -1, 1));
    return (int)$size * pow(1024, stripos('bkmgtpezy', $unit));
}

handleMethod("post", function($out) use ($uploaddir) {

    $error = function (string $error, bool $check, array $extra = []) use ($out) {
        if($check) $out->send(array_merge(["type" => "error", "error" => $error], $extra), 400);
    };

    if($_POST['action'] == "rename") {
        $error("resource_exists", file_exists($uploaddir.$_POST["to"]));
        $error("failed_rename", !@rename($uploaddir.$_POST["filename"], $uploaddir.$_POST["to"]));
    }

    if($_POST["action"] == "delete") {
       $error("failed_delete", !@unlink($uploaddir.$_POST["filename"]));
    }

    if($_POST["action"] == "new") {
        $error("topupldir_notfound", !file_exists(INCLUDES["UPLOADS"]) && !@mkdir(INCLUDES["UPLOADS"]));
        $error("siteupldir_notfound", !file_exists($uploaddir) && !@mkdir($uploaddir));
        $error("resource_exists", file_exists($uploaddir.$_POST["filename"]));
        $error("large_file", in_array($_FILES["file"]["error"], [ UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE ]));
        $error("failed_upl", !move_uploaded_file($_FILES["file"]["tmp_name"], $uploaddir.$_POST["filename"]));
    }

    $out->send(["type" => "success"], 200);
});

$theme->push("scripts", ["src" => "/phr-assets/js/uploads.js", "attrs" => "defer"]);

$uploads = new Template(INCLUDES["TPL"]."/admin/uploads.tpl");
$uploads->maxuplsize = parseSize(ini_get("upload_max_filesize"));
$uploads->maxformsize = parseSize(ini_get("post_max_size"));

foreach(glob("$uploaddir*") as $file) {
    $uploads->push("files", ["filename" => basename($file)]);
}

echo $uploads;