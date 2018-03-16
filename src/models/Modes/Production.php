<?php

namespace Phroses\Modes;

class Production extends Mode {
    protected $iniVars = [
        "display_errors" => 0,
		"error_reporting" => 0
    ];
}