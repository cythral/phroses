<?php

namespace Phroses\Modes;

abstract class Mode {
    protected $iniVars;

    const MODES = [
        "production" => "\Phroses\Modes\Production",
        "development" => "\Phroses\Modes\Development"
    ];

    public function setup() {
        $this->setIniVars();
    }

    private function setIniVars() {
        foreach($this->iniVars as $key => $val) {
            ini_set($key, $val);
        }
    }
}