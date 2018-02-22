<?php

namespace Phroses\Testing;

include __DIR__."/../src/constants.php";

// setup autoloader, functions
$loader = include \Phroses\ROOT."/vendor/autoload.php";
$loader->addPsr4("Phroses\\", \Phroses\SRC."/models");
include \Phroses\SRC."/functions.php";

define("Phroses\SITE", [
    "ID" => null,
    "NAME" => null,
    "THEME" => null,
    "ADMINURI" => null,
    "USERNAME" => null,
    "PASSWORD" => null,
    "PAGE" => null,
    "MAINTENANCE" => null
]);

class TestCase extends \PHPUnit\Framework\TestCase {
    public function assertArrayEquals($expected, $actual) {
        $this->assertEquals($expected, $actual, "\$canonicalize = true", $delta = 0.0, $maxDepth = 10, $canonicalize = true);
    }
}