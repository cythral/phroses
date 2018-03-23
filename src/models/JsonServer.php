<?php

namespace Phroses;

use \reqc\JSON\Server;
use \Phroses\Exceptions\ExitException;
use \Phroses\Routes\Controller as RouteController;

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

    public function restrict() {
        $this->error("access_denied", !$_SESSION, 401);
    }

    public function requireExistingPage() {
        $this->error("resource_missing", !in_array(Phroses::$response, [ RouteController::RESPONSES["PAGE"][200], RouteController::RESPONSES["PAGE"][301] ]));
    }

    public function requireChanges(array $keys) {
        $this->error("no_change", allKeysDontExist($keys, $_REQUEST));
    }
}