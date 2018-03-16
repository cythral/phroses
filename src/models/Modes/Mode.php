<?php

namespace Phroses\Modes;

abstract class Mode {
    use \Phroses\Traits\Properties;

    protected $iniVars;

    const TEST_CONST = "\Phroses\TESTING";
    
    const MODES = [
        "PRODUCTION" => "\Phroses\Modes\Production",
        "DEVELOPMENT" => "\Phroses\Modes\Development"
    ];

    const DB_CONFIG_DIRECTIVES = [
        "PRODUCTION" => "database",
        "TESTING" => "test-database"
    ];

    public function setup() {
        $this->setIniVars();
        $this->setDbDirective();
    }

    private function setIniVars() {
        foreach($this->iniVars as $key => $val) {
            ini_set($key, $val);
        }
    }

    private function setDbDirective() {
        $this->dbDirective = static::DB_CONFIG_DIRECTIVES["PRODUCTION"];

        if(defined(static::TEST_CONST) && inix::get(static::DB_CONFIG_DIRECTIVES["TESTING"])) {
            $this->dbDirective = static::DB_CONFIG_DIRECTIVES["TESTING"];
        }
    }
}