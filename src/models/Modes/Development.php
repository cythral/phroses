<?php

namespace Phroses\Modes;

use \reqc\Output;

class Development extends Mode {
    protected $iniVars = [
        "display_errors" => 1,
        "error_reporting" => E_ALL
    ];

    public function setup($noindex = true) {
        parent::setup();
        if($noindex) $this->setupNoIndex();
    }

    private function setupNoIndex() {
        (new Output)->setHeader("X-Robots-Tag", "none");
    }
}