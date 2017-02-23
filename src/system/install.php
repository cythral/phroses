<?php 
if($_SERVER["REQUEST_METHOD"] == "POST") {
  try {
    $db = new PDO("mysql:host=".$_POST["host"].";dbname=".$_POST["database"], $_POST["username"], $_POST["password"]);
    $db->query(file_get_contents(dirname(__DIR__)."/schema.sql"));
    
    $q = $db->prepare("INSERT INTO `sites` (`url`, `theme`, `name`,`adminUsername`, `adminPassword`) VALUES (?, 'bloom', ?, ?, ?)");
    $q->bindValue(1, $_SERVER["SERVER_NAME"]);
    $q->bindValue(2, $_POST["name"]);
    $q->bindValue(3, $_POST["susername"]);
    $q->bindValue(4, password_hash($_POST["spassword"], PASSWORD_DEFAULT));
    $q->execute();
    
    $c = file_get_contents("phroses.conf");
    $c = str_replace("<mode>", "production", $c);
    $c = str_replace("<host>", $_POST["host"], $c);
    $c = str_replace("<username>", $_POST["username"], $c);
    $c = str_replace("<password>", $_POST["password"], $c);
    $c = str_replace("<database>", $_POST["database"], $c);
    touch(dirname(__DIR__)."/phroses.conf");
    file_put_contents(dirname(__DIR__)."/phroses.conf", $c);
    
    http_response_code(301);
    header("location: /admin/pages");
    die;
  } catch(Exception $e) {
    $error = "Invalid database credentials.";
  }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
   <title>Install Phroses</title>
    <style>
      * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
        font-family: sans-serif;
      }
      .clear {
        clear: both;
      }
      .container, form {
        padding: 15px;
      }
      
      .field {
        margin: 15px;
      }
      .field label {
        width: 10%;
        display: inline-block;
        float: left;
        text-align: right;
        padding: 10px;
        height: 30px;
        line-height: 10px;
      }
      .field input {
        width: 90%;
        height: 30px;
        padding: 10px;
        float: left;
      }
    </style>
  </head>
  <body>
    <div class="container">
      <h1>
        Install Phroses
      </h1>
      <form action="" method="post">
        <?= $error ?? ""; ?>
        <h2>Database Credentials</h2>
        <div class="field">
          <label for="host">Host:</label>
          <input placeholder="Host" name="host" value="localhost" id="host" required>  
          <div class="clear"></div>
        </div>
        
        <div class="field">
          <label for="database">Database:</label>
          <input placeholder="Database" name="database" id="database" required>  
          <div class="clear"></div>
        </div>
        
        <div class="field">
          <label for="password">Username:</label>
          <input placeholder="Username" name="username" id="username" required>  
          <div class="clear"></div>
        </div>
        
         <div class="field">
          <label for="password">Password:</label>
          <input placeholder="Password" name="password" id="password" type="password">  
          <div class="clear"></div>
        </div>
        
        <h2>Site Credentials</h2>
        <div class="field">
          <label for="name">Site Name:</label>
          <input placeholder="name" name="name" id="name" required>
          <div class="clear"></div>
        </div>
        
        <div class="field">
          <label for="susername">Username:</label>
          <input placeholder="Username" name="susername" id="susername" required>
          <div class="clear"></div>
        </div>
        
        <div class="field">
          <label for="spassword">Password:</label>
          <input placeholder="Password" name="spassword" id="spassword" required type="password">
          <div class="clear"></div>
        </div>
        
        <button>
        Install
        </button>
      </form>  
    </div>
  </body>
</html>