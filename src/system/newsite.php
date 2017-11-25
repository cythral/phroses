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
    <style><?= file_get_contents(Phroses\SRC."/views/phr-assets/css/main.css"); ?></style>
  </head>
  <body class="aln-c">
    <div class="screen" id="install-welcome">
      <h1>Welcome</h1>
      <div></div>
    </div>
    <div id="install-flow">
      <h1 class="c">Create a Site</h1>
      <p>I couldn't find a site at <?= reqc\HOST; ?>, so I'll help you create one.</p>
      
      <form action="" method="post" id="flow-site">
        <div class="form_icfix c aln-l">
          <div>Site Name:</div>
          <input class="form_field form_input" placeholder="Phroses" name="name" id="name" required>
          <div class="clear"></div>
        </div>
        
        <div class="form_icfix c aln-l">
          <div>Username:</div>
          <input class="form_field form_input" placeholder="Username" name="username" id="susername" required autocomplete="new-password">
          <div class="clear"></div>
        </div>
        
        <div class="form_icfix c aln-l">
          <div>Password:</div>
          <input class="form_field form_input" placeholder="Password" name="password" id="spassword" required type="password" autocomplete="new-password">
          <div class="clear"></div>
        </div>
        <br>
        <button class="pst_btn txt">
        Submit
        </button>
      </form>
    </div>
    
    <div id="flow-success" class="screen">
      <h2 class="c">Success</h2>
    </div>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
    <script><?= file_get_contents(Phroses\SRC."/views/phr-assets/js/install.js"); ?></script>
  </body>
</html>
<?php die;