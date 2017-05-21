<?php


$p = new Phar(__DIR__.'/phroses.phar', 0, 'phroses');
$p->startBuffering();
$p->buildFromDirectory(__DIR__."/src/");
$p->setStub('<?php Phar::mapPhar("phroses"); include "phar://phroses/startup.php"; __HALT_COMPILER();');
$p->stopBuffering();