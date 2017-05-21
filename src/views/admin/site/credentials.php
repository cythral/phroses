<?php

use Phroses\DB;
use function Phroses\{HandleMethod, JsonOutput, JsonOutputSuccess};
use const Phroses\SITE;

HandleMethod("POST", function() {
  
  if($_POST["username"] == "") JsonOutput(["type" => "error", "error" => "bad_value", "field" => "username" ]);
	
  if($_POST["old"] != "" || $_POST["new"] != "" || $_POST["repeat"] != "") {
    if(!password_verify($_POST["old"], SITE["PASSWORD"])) JsonOutput(["type" => "error", "error" => "bad_value", "field" => "old"]);
    if($_POST["new"] != $_POST["repeat"]) JsonOutput(["type" => "error", "error" => "bad_value", "field" => "repeat"]);
    
    DB::Query("UPDATE `sites` SET `adminPassword`=? WHERE `id`=?", [
      password_hash($_POST["new"], PASSWORD_DEFAULT),
      SITE["ID"]
    ]);
  }
  
  DB::Query("UPDATE `sites` SET `adminUsername`=? WHERE `id`=?", [
    $_POST["username"],
    Phroses\SITE["ID"]
  ]);
  
  JsonOutputSuccess();
}, ["username", "old", "new", "repeat"]);
 
?>

<div class="container">
  <div>
		<a href="/admin/site" class="backbtn"><i class="fa fa-chevron-left"></i> Site Manager</a>
	</div>
  
  <br>
  
  <form id="phroses_site_creds" class="sys form" data-method="POST" data-uri="/admin/site/credentials">
    <h1>
      Change Site Credentials
    </h1>
    <div id="saved">Saved Credentials!</div>
    <div id="error"></div>
    
    <section>
      <div class="form_icfix">
          <div>Username:</div>
          <input name="username" required placeholder="Username" class="form_input form_field" autocomplete="off" value="<?= SITE["USERNAME"]; ?>">
      </div>
    </section>
    
    <section>
      <h2>
        Password
      </h2>
      <div class="form_icfix">
          <div>Old:</div>
          <input name="old" type="password" placeholder="Old Password" class="form_input form_field" autocomplete="off">
      </div>
      <div class="form_icfix">
          <div>New:</div>
          <input name="new" type="password" placeholder="New Password" class="form_input form_field" autocomplete="off">
      </div>
      <div class="form_icfix">
          <div>Repeat:</div>
          <input name="repeat" type="password" placeholder="Repeat Password" class="form_input form_field" autocomplete="off">
      </div>
    </section>
    <div class="aln-c">
      <button></button>
    </div>
  </form>
</div>