<?php

namespace Phroses\Traits;

trait UnpackOptions {
    public function unpackOptions($options, &$to) {
        if(defined("self::REQUIRED_OPTIONS")) {
            foreach(self::REQUIRED_OPTIONS as $required) {
                if(!array_key_exists(strtolower($required), array_change_key_case($options))) throw new \Exception("Missing required option $required");
            }
        }

        $to = $options;
    }
}