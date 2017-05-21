<?php

chdir(dirname(__DIR__));

// test cli version
echo "Starting Test: APP-CLI".PHP_EOL;
ob_start();
include "phar://phroses.phar";
ob_end_clean();
echo "APP-CLI: OK".PHP_EOL;
exit(0);