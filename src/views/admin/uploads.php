<?php

use phyrex\Template;
use function Phroses\{ handleMethod };
use const Phroses\{ INCLUDES };

$uploaddir = INCLUDES["UPLOADS"]."/".reqc\BASEURL."/";

handleMethod("post", function($out) use ($uploaddir) {
   if($_POST['action'] == "rename") {
       if(!@rename($uploaddir.$_POST["filename"], $uploaddir.$_POST["to"])) {
           $out->send(["type" => "error", "error" => "failed_rename"], 400);
       }
   }

   if($_POST["action"] == "delete") {
       if(!@unlink($uploaddir.$_POST["filename"])) {
           $out->send(["type" => "error", "error" => "failed_delete"]);
       }
   }

   if($_POST["action"] == "new") {
       if(!file_exists(INCLUDES["UPLOADS"]) && !@mkdir(INCLUDES["UPLOADS"])) {
           $out->send(["type" => "error", "error" => "topupldir_notfound"], 400);
       }

       if(!file_exists($uploaddir) && !@mkdir($uploaddir)) {
           $out->send(["type" => "error", "error" => "siteupldir_notfound"], 400);
       }

       if(file_exists($uploaddir.$_POST["filename"])) {
           $out->send(["type" => "error", "error" => "resource_exists"], 400);
       }

       if(!move_uploaded_file($_FILES["file"]["tmp_name"], $uploaddir.$_POST["filename"])) {
           $out->send(["type" => "error", "error" => "failed_upl"], 400);
       }
   }

   $out->send(["type" => "success"], 200);
});

$theme->push("scripts", ["src" => "/phr-assets/js/uploads.js", "attrs" => "defer"]);

$uploads = new Template(INCLUDES["TPL"]."/admin/uploads.tpl");

foreach(glob("$uploaddir*") as $file) {
    $uploads->push("files", ["filename" => basename($file)]);
}

echo $uploads;