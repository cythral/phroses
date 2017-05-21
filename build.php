<?php
function rcopy($src,$dst) { 
    $dir = opendir($src); 
    @mkdir($dst); 
    while(false !== ( $file = readdir($dir)) ) { 
        if (( $file != '.' ) && ( $file != '..' )) { 
            if ( is_dir($src . '/' . $file) ) { 
                rcopy($src . '/' . $file,$dst . '/' . $file); 
            } 
            else { 
                copy($src . '/' . $file,$dst . '/' . $file); 
            } 
        } 
    } 
    closedir($dir); 
} 

$p = new Phar(__DIR__.'/phroses.phar', 0, 'phroses');
$p->startBuffering();
$p->buildFromDirectory(__DIR__."/src/");
$p->setStub('<?php Phar::mapPhar("phroses"); include "phar://phroses/startup.php"; __HALT_COMPILER();');
$p->stopBuffering();

if(file_exists("phroses.tar")) unlink("phroses.tar");
if(file_exists("phroses.tar.gz")) unlink("phroses.tar.gz");

mkdir("themes.tmp");
rcopy("themes", "themes.tmp/themes");

$r = new PharData("phroses.tar");
$r->buildFromDirectory("themes.tmp");
$r->addFile("phroses.phar");
$r->addFile(".htaccess");
$r->addFile("schema.sql");
$r->addFile("deps.json");
$r->addFile("LICENSE");
$r->addFile("README.md");
$r = $r->compress(Phar::GZ);