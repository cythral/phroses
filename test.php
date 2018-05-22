<?php

$filename = "bower.json";

$filenameNoExt = pathinfo($filename, PATHINFO_FILENAME);
$matches = glob("{$filenameNoExt}.*");

if(count($matches) > 0) {
    foreach($matches as $file) {
        unlink($file);
    }
}