<?php

use listen\Events;
use Phroses\DB;

Events::listen("email", function($from = '', $to = '', $subject = '', $message = '') {
    DB::Query("REPLACE INTO `options` (`key`, `value`) VALUES (?, ?)", [ 'announcement', $message ]);
});