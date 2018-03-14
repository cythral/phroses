<?php
/**
 * Abstract class for creating singletons
 */

namespace Phroses\Patterns;

abstract class Singleton {
    static protected $instance;

    protected function __clone() {}
    protected function __wakeup() {}

    static public function getInstance(...$args): self {
        if(!static::$instance) {
            static::$instance = new static(...$args);
        }

        return static::$instance;
    }
}