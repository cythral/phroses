<?php

namespace Phroses\Traits;

trait Properties {
    protected $properties = [];

    /**
     * Getter 
     * 
     * @param string $key the name of the property to get
     * @return mixed the value of the specified key
     */
    public function __get(string $key) {
        if(method_exists($this, "get{$key}") && (new \ReflectionMethod($this, "get{$key}"))->isProtected()) {
            return $this->{"get{$key}"}();
        }
        
        return $this->properties[$key] ?? ((method_exists($this, "_get")) ? $this->_get($key) : null);
    }

    /**
     * Setter
     * 
     * @param string $key the name of the property to set
     * @param mixed $val the value of the property to set the key to
     * @return void
     */
    public function __set(string $key, $val) {
        if(method_exists($this, "set{$key}") && (new \ReflectionMethod($this, "set{$key}"))->isProtected()) {
            $val = $this->{"set{$key}"}($val);
            if(!$val) return;
        }

        if(method_exists($this, "_set")) $this->_set($key, $val);
        $this->properties[$key] = $val;
    }
}