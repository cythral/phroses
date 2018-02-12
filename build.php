<?php 

include __DIR__."/src/functions.php"; 
exec("lessc --clean-css src/views/assets/less/main.less src/views/assets/css/main.css"); 
copy("composer.json", "src/composer.json"); 
exec("cd src && composer update"); 

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
$p->setStub('#!/usr/bin/php'.PHP_EOL.'<?php 
@ob_end_clean(); Phar::mapPhar("phroses"); include "phar://phroses/startup.php"; __HALT_COMPILER();');
$p->stopBuffering(); 
if(file_exists("phroses.tar")) unlink("phroses.tar"); 
if(file_exists("phroses.tar.gz")) unlink("phroses.tar.gz"); 
if(file_exists("tmp")) Phroses\rrmdir("tmp"); 
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
Phroses\rrmdir("tmp"); 
Phroses\rrmdir("src/vendor"); 
unlink("src/composer.json"); 
unlink("phroses.tar"); 

echo "build complete".PHP_EOL;
