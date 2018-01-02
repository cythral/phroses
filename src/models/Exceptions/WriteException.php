<?php

namespace Phroses\Exceptions;

use \Exception;

class WriteException extends Exception {
    public $action;
    public $file;
    
    public function __construct(string $action, string $file) {
        $this->action = $action;
        $this->file = $file;
    }
}