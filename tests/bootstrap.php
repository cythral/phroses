<?php

include __DIR__."/../src/constants.php";

// setup autoloader, functions
$loader = include Phroses\ROOT."/vendor/autoload.php";
$loader->addPsr4("Phroses\\", Phroses\SRC."/models");
include Phroses\SRC."/functions.php";