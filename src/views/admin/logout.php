<?php

session_destroy();
session_write_close();

http_response_code(301);
header("location: /");
die;