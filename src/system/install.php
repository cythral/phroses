<?php 
$out = new \reqc\Output();
$out->setCode(404);

if(!is_writable(Phroses\ROOT)) {
  $out->setContentType(\reqc\MIME_TYPES["TXT"]);
  echo "No write access to ".Phroses\ROOT.". Please fix directory permissions";
  exit(1);
}
  
Phroses\HandleMethod("POST", function() {
  $out = new \reqc\JSON\Server();
  try {

    // setup database
    $db = new PDO("mysql:host=".$_POST["host"].";dbname=".$_POST["database"], $_POST["username"], $_POST["password"]);
    if(version_compare($db->query("select version()")->fetchColumn(), Phroses\DEPS["MYSQL"], "<")) throw new Exception("version");
    $schema = new phyrex\Template(Phroses\SRC."/schema/install.sql");
    $schema->schemaver = Phroses\SCHEMAVER;
    $db->query($schema);
    
    // setup configuration file
    $c = new phyrex\Template(Phroses\SRC."/phroses.conf");
    $c->mode = (INPHAR) ? "production" : "development";
    $c->host = $_POST["host"];
    $c->username = $_POST["username"];
    $c->password = $_POST["password"];
    $c->database = $_POST["database"];
    
    file_put_contents(Phroses\ROOT."/phroses.conf", $c);
    chown(Phroses\ROOT."/phroses.conf", posix_getpwuid(posix_geteuid())['name']);
    chmod(Phroses\ROOT."/phroses.conf", 0775);
    
    $out->send(["type" => "success"], 200);

  } catch(Exception $e) {
    $output = [ "type" => "error", "error" => "credentials" ];
    
    if($e->getMessage() == "version") {
      $output["error"] = "version";
      $output["minver"] = Phroses\DEPS["MYSQL"];
    }
    
    $out->send($output, 400);
  }
}, ["host", "database", "username", "password"]);
  

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
   <title>Install Phroses</title>
    <style><?= file_get_contents(Phroses\SRC."/views/phr-assets/css/main.css"); ?></style>
  </head>
  <body class="aln-c">
    <div class="screen" id="install-welcome">
      <h1>Welcome</h1>
      <div></div>
    </div>
    <div id="install-flow">
      <h1 class="c">Install Phroses</h1>
      
      <form class="flow" id="flow-db" action="" method="post">
        <h2>1. Setup Database</h2>
        <p>I need your database credentials</p>
        
        <div class="form_icfix c aln-l">
          <div>Host:</div>
          <input class="form_field form_input" placeholder="Host" name="host" value="localhost" id="host" required autocomplete="off">  
          <div class="clear"></div>
        </div>

        <div class="form_icfix c aln-l">
          <div>Database:</div>
          <input class="form_field form_input" placeholder="Database" name="database" id="database" required autocomplete="off">  
          <div class="clear"></div>
        </div>

        <div class="form_icfix c aln-l">
          <div>Username:</div>
          <input class="form_field form_input" placeholder="Username" name="username" id="username" required autocomplete="new-password">  
          <div class="clear"></div>
        </div>

         <div class="form_icfix c aln-l">
          <div>Password:</div>
          <input class="form_field form_input" placeholder="Password" name="password" id="password" type="password" autocomplete="new-password">  
          <div class="clear"></div>
        </div>
        <br>
        <input type="submit" class="pst_btn txt" value="Next">
      </form>
     
      <form action="" method="post" id="flow-site">
        <h2>2. Site Specifics</h2>
        <p>Lets set up your site together xoxo</p>
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
        Install
        </button>
      </form>
    </div>
    
    <div id="flow-db-error" class="screen">
      <h2 class="c">Oops..</h2>
      <br>
      A minimum MySQL version of <span id="flow-db-error-ver"></span> is required to run Phroses.
    </div>
    
    <div id="flow-success" class="screen">
      <h2 class="c">Success</h2>
    </div>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
    <script><?= file_get_contents(Phroses\SRC."/views/phr-assets/js/install.js"); ?></script>
  </body>
</html>