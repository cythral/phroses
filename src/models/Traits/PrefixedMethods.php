<?php

namespace Phroses\Traits;

use function \Phroses\{ stringStartsWith };

trait PrefixedMethods {

    /**
     * Calls all methods with a prefix
     * 
     * @param string $prefix the prefix to call
     * @param array $args an array of arguments to pass to each prefixed method
     * @return int the number of methods called
     */
    protected function callPrefixedMethods(string $prefix, array $args = []) {
        $count = 0;

        foreach(get_class_methods($this) as $method) {
            if(stringStartsWith($method, $prefix)) {
                $this->{$method}(...$args);
                $count;
            }
        }

        return $count;
    }

}