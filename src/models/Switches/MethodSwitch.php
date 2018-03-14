<?php

namespace Phroses\Switches;

use \Phroses\Switcher;
use \reqc\Output;
use const \reqc\{ METHOD };

class MethodSwitch extends Switcher {

    public function __construct(?string $method = null, array $defaultArgs = []) {
        parent::__construct(strtolower($method ?? METHOD), $defaultArgs);
    }

    public function case($key, callable $func, array $args = [], $out = Output::class): Switcher {
        if(empty($args)) $args = $this->defaultArgs;
        array_unshift($args, new $out);
        return parent::case($key, $func, $args);
    }

}