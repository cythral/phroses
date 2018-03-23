<?php
/**
 * Class to sanitize each element of an array with callbacks
 */

namespace Phroses;

class Sanitizer {
    private $data;

    const DEFAULT_CALLBACKS = [
        "urldecode",
        "trim"
    ];

    /**
     * Constructs a new Sanitizer object
     * 
     * @param array $data the array to sanitize
     * @param array $callbacks an array of default callbacks to apply to every element
     */
    public function __construct(array $data, array $callbacks = self::DEFAULT_CALLBACKS) {
        $this->data = $data;
        $this->callbacks = $callbacks;
    }

    /**
     * Applies a callback to the entire array or only some keys
     * 
     * @param callable $callback the callback to apply
     * @param array $keys the array keys to affect, null for every key
     * @return void
     */
    public function applyCallback(callable $callback, ?array $keys = null): void {
        $keys = $keys ?? array_keys($this->data);

        foreach($keys as $key) {
            if(!isset($this->data[$key])) continue;
            $this->data[$key] = $callback($this->data[$key]);
        }
    }

    /**
     * Applies default callbacks
     * 
     * @return array the sanitized array
     */
    public function __invoke(): array {
        foreach($this->callbacks as $callback) {
            $this->applyCallback($callback);
        }

        return $this->data;
    }
}