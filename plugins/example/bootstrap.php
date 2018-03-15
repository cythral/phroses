<?php

use listen\Events;
use Phroses\DB;

Events::listen("email", function($from = '', $to = '', $subject = '', $message = '') {
    \Phroses\Database\Database::getInstance()->prepare("REPLACE INTO `options` (`key`, `value`) VALUES (?, ?)", [ 'announcement', $message ]);
});