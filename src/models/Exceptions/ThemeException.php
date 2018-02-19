<?php

namespace Phroses\Exceptions;

use \Exception;

const THEME_ERRORS = [
    "NOT_FOUND" => "The theme {name} does not exist",
    "NO_TEMPLATE" => "The template/type {type} was not found in the theme {name}"
];

class ThemeException extends Exception {
    public function __construct($message, $name, $type = "") {
        $this->originalError = $message;
        $this->message = str_replace(["{name}", "{type}"], [$name, $type], $message);
    }
}