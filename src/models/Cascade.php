<?php

namespace Phroses;

class Cascade {
    private $rules;
    private $result;

    public function __construct($initialValue) {
        $this->result = $initialValue;
    }

    /**
     * Simple cascade, if expression evaluates to true the result is overridden with the new value
     * 
     * @param bool $expr the expression to evaluate
     * @param mixed $value the value to set the result to
     */
    public function addRule(bool $expr, $value): void {
        if($expr) $this->result = $value;
    }

    /**
     * Getter for the result property
     * 
     * @return mixed the value of the result property
     */
    public function getResult() {
        return $this->result;
    }
}