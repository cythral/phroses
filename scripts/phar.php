<?php

include __DIR__."/../src/functions.php";

$p = new Phar(__DIR__.'/../build/phroses.phar', 0, 'phroses'); 
$p->startBuffering(); 
$p->buildFromDirectory(__DIR__."/../build/src/"); 
$p->setStub('#!/usr/bin/php'.PHP_EOL.'<?php @ob_end_clean(); Phar::mapPhar("phroses"); include "phar://phroses/phroses.php"; __HALT_COMPILER();');
$p->stopBuffering(); 

Phroses\rrmdir(__DIR__."/../build/src");

$r = new PharData(__DIR__."/../phroses.tar");
$r->buildFromDirectory(__DIR__."/../build");
$r = $r->compress(Phar::GZ);