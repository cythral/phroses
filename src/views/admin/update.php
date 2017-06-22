<?php
use function Phroses\{sendEvent, rrmdir};
use const Phroses\{ROOT, VERSION, INCLUDES, IMPORTANT_FILES};

$options  = array('http' => array(
  'user_agent'=> $_SERVER["HTTP_USER_AGENT"]
));
$context  = stream_context_create($options);
$version = json_decode(@file_get_contents("http://api.phroses.com/version", false, $context))->latest_version ?? null;

if($version == null) { ?>
<div id="phr-update-apier" class="container aln-c phr-update c">
  <h1>
    Having some trouble accessing the API.  Please try again later.
  </h1>
</div>
<? } else if(version_compare(VERSION, $version, "<")) {
  
  if(isset($_GET["start_upgrade"])) {
    ob_end_clean();
    ob_end_clean();
    header("Content-Type: text/event-stream\n\n");
    
    if(!is_writable(ROOT) || (file_exists(ROOT."/phroses.tar.gz") && !unlink(ROOT."/phroses.tar.gz"))) {
      sendEvent("error", ["error" => "write"]);
      die;
    }
    
    try {
      chdir(ROOT);
      touch("./.down");
      // backup 
      if(!file_exists("tmp") && !mkdir("tmp")) throw new Exception("write");
      foreach(IMPORTANT_FILES as $backup) {
        if(is_dir(ROOT."/$backup")) {
          if(!rename(ROOT."/$backup", ROOT."/tmp/$backup")) throw new Exception("write");
        } else if(!copy($backup, "tmp/$backup")) throw new Exception("write");
      }
      sendEvent("progress", [ "progress" => 10 ]);
      sleep(3);
      // download and extract
      if(!($api = @fopen("http://api.phroses.com/downloads/$version.tar.gz", "r"))) throw new Exception("api");
      if(!@file_put_contents("phroses.tar.gz", $api)) throw new Exception("write");
      $archive = new PharData("phroses.tar.gz");
      $archive->extractTo(ROOT, null, true);
      if(!unlink("phroses.tar.gz")) throw new Exception("write5");
      sendEvent("progress", [ "progress" => 30 ]);
      
      // cleanup
      if(!rrmdir(INCLUDES["THEMES"])) throw new Exception("write");
      if(!rename("tmp/themes", Phroses\INCLUDES["THEMES"])) throw new Exception("write");
      if(!rename("tmp/phroses.conf", "phroses.conf")) throw new Exception ("write");
      if(!rrmdir("tmp")) throw new Exception("write");
      sendEvent("progress", [ "progress" => 70 ]);
      
      // finish update
      shell_exec("php phroses.phar update");
      sendEvent("progress", [ "progress" => 100, "version" => $version ]);
      
    } catch(PharException $e) {
      sendEvent("error", ["error" => "extract"]);
      
    } catch(Exception $e) {
      sendEvent("error", ["error" => $e->getMessage() ]);     
   
    } finally {
      unlink(ROOT."/.down");
      // if error occurred and tmp dir still exists, move everything in tmp back
      // todo: add error checking here
      if(file_exists("tmp")) {
        foreach(IMPORTANT_FILES as $file) {
          if(file_exists("tmp/$file")) {
            if(is_dir("tmp/$file")) { 
              if(file_exists($file)) rrmdir($file);
              rename("tmp/$file", $file);
            
            } else {
              if(file_exists($file)) unlink($file);
              rename("tmp/$file", $file);
            }
          }
        }
        unlink("tmp");
      }
    }
    
    die;
  }
  ?>

  <div id="phr-update-avail" class="container aln-c phr-update">
    <h1 class="c">
      An update is available
    </h1>
    <div class="phr-update-icon">
      <img src="/phr-assets/img/update-ring.png">
      <img src="/phr-assets/img/update-arrow.png">
    </div>
    <p class="c">
      click the above icon to start updating
    </p>
  </div>

  <div id="phr-upgrade-screen" class="container screen">
    <h1>
      Updating Phroses
    </h1>
    <div class="phr-progress"><div class="phr-progress-bar"></div></div>
    <p class="phr-progress-error"></p>
  </div>

<? } else { ?>
  <div id="phr-update-noavail" class="container aln-c phr-update c">
    <h1>
      Phroses is up-to-date
      <div>
        <img src="/phr-assets/img/checkmark.png" style="width:250px;height:250px;">
      </div>
    </h1>
  </div>
 <? }