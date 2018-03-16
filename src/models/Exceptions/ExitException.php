<?php

namespace Phroses\Exceptions;

use \Exception;

class ExitException extends Exception {
    public $code;

    public function __construct(int $code = 0, string $message = "") {
        $this->code = $code;
        $this->message = $message;
    }

    public function defaultHandler() {
        if(!empty($this->message)) echo $message;
        exit($this->code);
    }
}