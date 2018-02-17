<?php
use Phroses\Phroses;
use Phroses\Exceptions\WriteException;
use function Phroses\{sendEvent, rrmdir};
use const Phroses\{ROOT, VERSION, INCLUDES, IMPORTANT_FILES};

ob_end_clean();
ob_end_clean();
ob_end_clean(); // third times the charm

ini_set("memory_limit", "50M");
self::$out = new reqc\EventStream\Server();

if(!is_writable(ROOT) || (file_exists(ROOT."/phroses.tar.gz") && !unlink(ROOT."/phroses.tar.gz"))) {
    self::$out->send("error", ["error" => "write"]);
    die;
}

try {
    chdir(ROOT);
    
    self::setMaintenance(self::MM_ON);
    
    $version = json_decode(@file_get_contents("http://api.phroses.com/version"))->latest_version ?? null;
    if(!$version) throw new Exception("api");

    // backup 
    if(!file_exists("tmp") && !mkdir("tmp")) throw new WriteException("create", ROOT."/tmp/");
    foreach(IMPORTANT_FILES as $backup) {
        if(is_dir(ROOT."/$backup")) {
            if(!rename(ROOT."/$backup", ROOT."/tmp/$backup")) throw new WriteException("move", ROOT."/tmp/$backup");
        } else if(!copy($backup, "tmp/$backup")) throw new WriteException("move", ROOT."/tmp/$backup");
    }
    self::$out->send("progress", [ "progress" => 10 ]);
    
    // download and extract
    if(!($api = @fopen("http://api.phroses.com/downloads/$version.tar.gz", "r"))) throw new Exception("api");
    if(!@file_put_contents("phroses.tar.gz", $api)) throw new WriteException("create", ROOT."/phroses.tar.gz");
    $archive = new PharData("phroses.tar.gz");
    $archive->extractTo(ROOT, null, true);
    if(!unlink("phroses.tar.gz")) throw new WriteException("delete", ROOT."/phroses.tar.gz");
    self::$out->send("progress", [ "progress" => 30 ]);
    
    // cleanup
    if(!rrmdir(INCLUDES["THEMES"])) throw new WriteException("delete", INCLUDES["THEMES"]);
    if(!rrmdir(INCLUDES["PLUGINS"])) throw new WriteException("delete", INCLUDES["PLUGINS"]);
    self::$out->send("progress", [ "progress" => 40 ]);

    if(!rename("tmp/themes", INCLUDES["THEMES"])) throw new WriteException("restore", INCLUDES["THEMES"]);
    if(!rename("tmp/plugins", INCLUDES["PLUGINS"])) throw new WriteException("restore", INCLUDES["PLUGINS"]);
    if(!rename("tmp/phroses.conf", "phroses.conf")) throw new WriteException("restore", ROOT."/tmp/phroses.conf");
    if(!rrmdir("tmp")) throw new WriteException("delete", ROOT."/tmp");
    self::$out->send("progress", [ "progress" => 70 ]);
    
    // finish update
    @shell_exec("php phroses.phar update");
    self::$out->send("progress", [ "progress" => 100, "version" => $version ]);
    
} catch(PharException $e) {
    self::$out->send("error", ["error" => "extract"]);

} catch(WriteException $e) {
    self::$out->send("error", ["error" => "write", "action" => $e->action, "file" => $e->file ]);
    
} catch(Exception $e) {
    self::$out->send("error", ["error" => $e->getMessage() ]);     

} finally {
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
    
    self::setMaintenance(self::MM_OFF);
}

die;