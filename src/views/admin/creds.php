<?php

use phyrex\Template;
use Phroses\DB;
use function Phroses\{ handleMethod };
use const Phroses\{ SITE, INCLUDES };

handleMethod("post", function($out) {
  if($_POST["username"] == "") $out->send(["type" => "error", "error" => "bad_value", "field" => "username" ], 400);
	
  if($_POST["old"] != "" || $_POST["new"] != "" || $_POST["repeat"] != "") {
    if(!password_verify($_POST["old"], SITE["PASSWORD"])) $out->send(["type" => "error", "error" => "bad_value", "field" => "old"], 400);
    if($_POST["new"] != $_POST["repeat"]) $out->send(["type" => "error", "error" => "bad_value", "field" => "repeat"], 400);
    
    DB::Query("UPDATE `sites` SET `adminPassword`=? WHERE `id`=?", [
      password_hash($_POST["new"], PASSWORD_DEFAULT),
      SITE["ID"]
    ]);
  }
  
  DB::Query("UPDATE `sites` SET `adminUsername`=? WHERE `id`=?", [
    $_POST["username"],
    SITE["ID"]
  ]);
  
  $out->send(["type" => "success"], 200);
  
}, ["username", "old", "new", "repeat"]);
 

$creds = new Template(INCLUDES["TPL"]."/admin/creds.tpl");
$creds->username = SITE["USERNAME"];
echo $creds;
