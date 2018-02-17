<?php 

/**
 * This file builds a packaged phar archive from the src directory
 */


include __DIR__."/src/functions.php"; 

// this is done in travis but in case we are building without travis, compile less anyways
exec("lessc --clean-css src/views/assets/less/main.less src/views/assets/css/main.css");

// package dependencies into the phar
copy("composer.json", "src/composer.json"); 
exec("cd src && composer update"); 

use function \Phroses\{ rrmdir, rcopy };

// create the phar
$p = new Phar(__DIR__.'/phroses.phar', 0, 'phroses'); 
$p->startBuffering(); 
$p->buildFromDirectory(__DIR__."/src/"); 
$p->setStub('#!/usr/bin/php'.PHP_EOL.'<?php @ob_end_clean(); Phar::mapPhar("phroses"); include "phar://phroses/phroses.php"; __HALT_COMPILER();');
$p->stopBuffering(); 

// remove files in case they exist
if(file_exists("phroses.tar")) unlink("phroses.tar"); 
if(file_exists("phroses.tar.gz")) unlink("phroses.tar.gz"); 
if(file_exists("tmp")) rrmdir("tmp"); // empty the tmp dir if it exiss

// make tmp dir, copy themes and plugins there so they can be recursively added to the tar archive
mkdir("tmp"); 
rcopy("themes", "tmp/themes"); 
rcopy("plugins", "tmp/plugins"); 
exec("chmod -R 775 tmp/themes"); 
chmod(".htaccess", 0775); 

// create the tar archive
$r = new PharData("phroses.tar"); 
$r->buildFromDirectory("tmp"); 
$r->addFile("phroses.phar"); 
$r->addFile(".htaccess", ".htaccess"); 
$r->addFile("LICENSE"); 
$r->addFile("README.md"); 
$r = $r->compress(Phar::GZ);  // gzip it

// cleanup
rrmdir("tmp"); 
rrmdir("src/vendor"); 
unlink("src/composer.json"); 
unlink("phroses.tar"); 

echo "BUILD COMPLETE".PHP_EOL;