<?php 

include __DIR__."/src/functions.php"; 
exec("lessc --clean-css src/views/assets/less/main.less src/views/assets/css/main.css"); 
copy("composer.json", "src/composer.json"); 
exec("cd src && composer update"); 

use function \Phroses\{ rrmdir, rcopy };

$p = new Phar(__DIR__.'/phroses.phar', 0, 'phroses'); 
$p->startBuffering(); 
$p->buildFromDirectory(__DIR__."/src/"); 
$p->setStub('#!/usr/bin/php'.PHP_EOL.'<?php @ob_end_clean(); Phar::mapPhar("phroses"); include "phar://phroses/phroses.php"; __HALT_COMPILER();');
$p->stopBuffering(); 

if(file_exists("phroses.tar")) unlink("phroses.tar"); 
if(file_exists("phroses.tar.gz")) unlink("phroses.tar.gz"); 
if(file_exists("tmp")) rrmdir("tmp");

mkdir("tmp"); 
rcopy("themes", "tmp/themes"); 
rcopy("plugins", "tmp/plugins"); 
exec("chmod -R 775 tmp/themes"); 
chmod(".htaccess", 0775); 

$r = new PharData("phroses.tar"); 
$r->buildFromDirectory("tmp"); 
$r->addFile("phroses.phar"); 
$r->addFile(".htaccess", ".htaccess"); 
$r->addFile("LICENSE"); 
$r->addFile("README.md"); 
$r = $r->compress(Phar::GZ); 

rrmdir("tmp"); 
rrmdir("src/vendor"); 
unlink("src/composer.json"); 
unlink("phroses.tar"); 

echo "build complete".PHP_EOL;
