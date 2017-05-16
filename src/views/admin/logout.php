<?php

Phroses\Session::end();

http_response_code(301);
header("location: /");
die;