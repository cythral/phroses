<?php

use phyrex\Template;
use function \Phroses\{ handleMethod };
use const \Phroses\{ SITE, INCLUDES };

if($_SESSION) self::$out->redirect("/admin");

handleMethod("POST", function($out) {

  if(password_verify($_POST["password"], SITE["PASSWORD"]) && $_POST["username"] == SITE["USERNAME"]) {
    $_SESSION["live"] = "true";
    $out->send(["type" => "success"], 200);
  }

  $out->send(["type" => "error"], 401);

});


echo new Template(INCLUDES["TPL"]."/admin/login.tpl");
