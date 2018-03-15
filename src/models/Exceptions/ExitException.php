<?php

namespace Phroses\Exceptions;

use \Exception;

class ExitException extends Exception {
    public $code;

    public function __construct(int $code = 0) {
        $this->code = $code;
    }

    public function defaultHandler() {
        exit($this->code);
    }
}