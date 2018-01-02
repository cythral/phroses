<?php

Phroses\Session::end();

self::$out->setCode(301);
header("location: /");
die;