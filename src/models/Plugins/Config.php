<?php

namespace Phroses\Plugins;

use \InvalidArgumentException;

class Config {
    use \Phroses\Traits\Properties;

    private $file;

    public function __construct(string $file) {
        if(!file_exists($file)) {
            throw new InvalidArgumentException("\$file must be an accessible file");
        }

        $this->file = $file;
        $this->load();
    }

    public function load(): self {
        $this->properties = json_decode(file_get_contents($this->file), true);
        return $this;
    }

    public function save(): bool {
        return file_put_contents($this->file, json_encode($this->properties));
    }

    protected function _set(string $key, string $val) {
        $this->properties[$key] = $val;
        $this->save();
    }
}