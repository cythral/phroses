<?php
use Phroses\Events;
use Phroses\DB;

Events::Listen("email", function($from = '', $to = '', $subject = '', $message = '') {
    DB::Query("REPLACE INTO `options` (`key`, `value`) VALUES (?, ?)", [ 'announcement', $message ]);
});