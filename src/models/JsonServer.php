<?php

namespace Phroses;

use \reqc\JSON\Server;
use \Phroses\Exceptions\ExitException;

class JsonServer extends Server {
    public function success(int $code = 200, array $extra = []) {
        $this->send(array_merge(["type" => "success"], $extra), $code, false);
        throw new ExitException(0);
    }
    
    public function error(string $error, bool $condition = true, int $code = 400, array $extra = []) {
        if($condition) {
            $this->send(array_merge([ "type" => "error", "error" => $error ], $extra), $code, false);
            throw new ExitException(1);
        }
    }
}