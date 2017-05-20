<?php

Phroses\HandleMethod("POST", function() {
  Phroses\DB::Query("INSERT INTO `sites` (`url`, `theme`, `name`,`adminUsername`, `adminPassword`) VALUES (?, 'bloom', ?, ?, ?)", [
    strtok($_SERVER["HTTP_HOST"], ":"),
    $_POST["name"],
    $_POST["username"],
    password_hash($_POST["password"], PASSWORD_DEFAULT)
  ]);
  
  JsonOutputSuccess();
}, ["name", "username", "password"]);

http_response_code(404);
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Create Phroses Site</title>
    <style><?= file_get_contents(Phroses\SRC."/system/style.css"); ?></style>
  </head>
  <body>
    <header>
      <img src="data:image/png;base64,<?= base64_encode(file_get_contents(Phroses\INCLUDES["VIEWS"]."/phroses-logo.png")); ?>" alt="Phroses Logo">
      <h1>
        MultiCMS by Cythral&reg;
      </h1>
    </header>
    <div class="container">
      <form action="" method="POST">
        <h1>No site found. Create one?</h1>
        <div class="field">
          <label for="name">Name:</label>
          <input id="name" name="name" placeholder="Website title/name" required>
          <div class="clear"></div>
        </div>
        <div class="field">
          <label for="username">Username:</label>
          <input id="username" name="username" placeholder="Admin Username" required>
          <div class="clear"></div>
        </div>
        <div class="field">
          <label for="password">Password:</label>
          <input id="password" name="password" type="password" placeholder="Admin Password" required>
          <div class="clear"></div>
        </div>
        
        <div class="aln-c">
          <button style="background: url(data:image/png;base64,<?= base64_encode(file_get_contents(Phroses\INCLUDES["VIEWS"]."/admin/next.png")); ?>);"></button>
        </div>
      </form>  
    </div>
  </body>
</html>
<?php die;