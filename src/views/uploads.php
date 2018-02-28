<?php

use Phroses\Phroses;
use phyrex\Template;
use function Phroses\{ handleMethod, parseSize, mapError };
use const Phroses\{ INCLUDES, SITE };

$uploaddir = INCLUDES["UPLOADS"]."/".reqc\BASEURL."/";

handleMethod("post", function($out) use ($uploaddir) {
    
    if($_POST['action'] == "rename") {
        mapError("resource_exists", file_exists($uploaddir.$_POST["to"]));
        mapError("failed_rename", !@rename($uploaddir.$_POST["filename"], $uploaddir.$_POST["to"]));
    }

    if($_POST["action"] == "delete") {
       mapError("failed_delete", !@unlink($uploaddir.$_POST["filename"]));
    }

    if($_POST["action"] == "new") {
        mapError("topupldir_notfound", !file_exists(INCLUDES["UPLOADS"]) && !@mkdir(INCLUDES["UPLOADS"]));
        mapError("siteupldir_notfound", !file_exists($uploaddir) && !@mkdir($uploaddir));
        mapError("resource_exists", file_exists($uploaddir.$_POST["filename"]));
        mapError("large_file", in_array($_FILES["file"]["error"], [ UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE ]));
        mapError("failed_upl", !move_uploaded_file($_FILES["file"]["tmp_name"], $uploaddir.$_POST["filename"]));
    }

    $out->send(["type" => "success"], 200);
});

//$page->theme->push("scripts", ["src" => $site->adminURI."/assets/js/uploads.js", "attrs" => "defer"]);

$uploads = new Template(INCLUDES["TPL"]."/admin/uploads.tpl");
$uploads->maxuplsize = parseSize(ini_get("upload_max_filesize"));
$uploads->maxformsize = parseSize(ini_get("post_max_size"));

foreach(glob("$uploaddir*") as $file) {
    $uploads->push("files", ["filename" => basename($file)]);
}

echo $uploads;