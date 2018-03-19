<?php

namespace Phroses\Exceptions;

use \Exception;

class ExitException extends Exception {
    const HTTP_RESPONSE_CODES = [
        0 => 200,
        1 => 500,
        127 => 404 // 127 = command not found
    ];

    public $code;

    public function __construct(int $code = 0, ?string $message = "") {
        $this->code = $code;
        $this->message = $message;
    }

    public function defaultHandler() {
        if(array_key_exists($this->code, self::HTTP_RESPONSE_CODES)) {
            http_response_code(self::HTTP_RESPONSE_CODES[$this->code]);
        }

        if(!empty($this->message)) {
            echo $this->message;
        }
        
        exit($this->code);
    }
}