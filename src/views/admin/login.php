<?php
namespace Phroses;


if($_SESSION) self::$out->redirect("/admin");

// todo: switch to json
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


?>

<form id="phroses-login">
  <h2>Login to Phroses Site Panel</h2>
  <div><input name="username" type="text" placeholder="Username"></div>
  <div><input name="password" type="password" placeholder="Password"></div>
  <div><input type="submit" value="Login"></div>
</form>