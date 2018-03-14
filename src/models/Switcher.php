<?php
/**
 * Object oriented switching by value because normal switch syntax is ugly
 */
namespace Phroses;

class Switcher {
    private $value;
    protected $defaultArgs = [];
    private $result;
    private $resolved = false;

    public function __construct($value, array $defaultArgs = []) {
        $this->value = $value;
        $this->defaultArgs = $defaultArgs;
    }
 
    public function case($key, callable $func, array $args = []): self {
        $args = (!empty($args)) ? $args : $this->defaultArgs;

        if((!$this->resolved) && ($this->value == $key || (is_array($key) && in_array($this->value, $key)) || $key == null)) {            
            $this->result = $func(...$args);
            $this->resolved = true;
        }

        return $this;
    }

    public function getResult() {
        return $this->result;
    }

    public function isResolved() {
        return $this->resolved;
    }
}