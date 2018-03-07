<?php

define("ROOT", dirname(__DIR__));
include ROOT."/src/functions.php";

$p = new Phar(ROOT.'/build/phroses.phar', 0, 'phroses'); 
$p->startBuffering(); 
$p->buildFromDirectory(ROOT."/build/src/");
$time = time();
$p->setStub('#!/usr/bin/php'.PHP_EOL.'<?php @ob_end_clean(); Phar::mapPhar("phroses"); define("Phroses\BUILD_TIMESTAMP", '.$time.'); include "phar://phroses/phroses.php"; __HALT_COMPILER();');
$p->stopBuffering(); 

Phroses\rrmdir(ROOT."/build/src");

$r = new PharData(ROOT."/phroses.tar");
$r->buildFromDirectory(ROOT."/build");
$r = $r->compress(Phar::GZ);
unlink(ROOT."/phroses.tar");