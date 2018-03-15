<?php

namespace Phroses\Commands;

use \InvalidArgumentException;
use function \Phroses\stringStartsWith;

class Flag {
    use \Phroses\Traits\Properties;
    
    const PREFIX = "--";
    const VALUE_SEPARATOR = "=";

    public function __construct($name, $value) {
        $this->name = $name;
        $this->value = $value;
    }

    static public function parse(string $arg): self {
        if(!stringStartsWith($arg, self::PREFIX)) {
            throw new InvalidArgumentException("Not a flag");
        }

        $arg = substr($arg, strlen(self::PREFIX));
        $key = strstr($arg, self::VALUE_SEPARATOR, true);
        $value = strstr($arg, self::VALUE_SEPARATOR, false);
        $value = ($value) ? substr($value, strlen(self::VALUE_SEPARATOR)) : true;

        return new self($key, $value);
    }
}