<?php
namespace Phroses;

HandleMethod("POST", function() {
  header("content-type: text/plain");
 
  if(password_verify($_POST["password"], SITE["PASSWORD"]) && $_POST["username"] == SITE["USERNAME"]) {
    $_SESSION["live"] = "true";
    echo "success";
    return;
  }

  http_response_code(401);
  echo "fail";
});