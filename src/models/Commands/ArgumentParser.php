<?php
/**
 * Iterates an array and separates flags from regular arguments
 */

namespace Phroses\Commands;

use \InvalidArgumentException;
use \Phroses\Commands\Flag;
use function \Phroses\stringStartsWith;

class ArgumentParser {
    private $args;

    public function __construct(array $args) {
        $this->args = $args;
    }

    public function parse(): array {
        $parsed = [
            "args" => [],
            "flags" => []
        ];

        foreach($this->args as $arg) {
            if($this->isFlag($arg)) {
                $flag = Flag::parse($arg);
                $parsed["flags"][$flag->name] = $flag;
            } else $parsed["args"][] = $arg;
        }

        return $parsed;
    }

    private function isFlag(string $arg): bool {
        return stringStartsWith($arg, Flag::PREFIX);
    }
}