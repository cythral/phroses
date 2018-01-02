<?php
namespace Phroses;

// TODO: SWITCH TO JSON


HandleMethod("POST", function($out) {
  header("content-type: text/plain");
 
  if(password_verify($_POST["password"], SITE["PASSWORD"]) && $_POST["username"] == SITE["USERNAME"]) {
    $_SESSION["live"] = "true";
    echo "success";
    return;
  }

  $out->setCode(401);
  echo "fail";
});