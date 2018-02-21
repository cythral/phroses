<?php

namespace Phroses\Theme\Loaders;

use \Phroses\Theme\Loader;

class DummyLoader implements Loader {
    public $types = [ "page" => true ];
    public $errors = [];
    public $assets = [];
    public $folders = [];
    public $exists = true;
    public $api = true;

    static public $list = [];
    
    public function __construct($options = []) {
        foreach(["name", "types", "exists", "errors", "assets", "folders", "api" ] as $option) {
            if(isset($options[$option])) {
                $this->{$option} = $options[$option];
            }
        }
    }
    
    public function exists(): bool {
        return $this->exists;
    }
    
    public function hasType(string $type): bool {
        return array_key_exists($type, $this->types);
    }
    
    public function hasFolder(string $folder): bool {
        return array_search($folder, $this->folders) !== false ? true : false;
    }
    
    public function hasAsset(string $asset): bool {
        return array_key_exists($asset, $this->assets);    
    }
    
    public function hasError(string $error): bool {
        return array_key_exists($error, $this->errors);    
    }

    public function hasApi(): bool {
        return $this->api;
    }
    
    public function getName(): string {
        return $this->name;
    }
    
    public function getType(string $type): ?string {
        return $this->types[$type] ?? null;
    }
    
    public function getTypes(): array {
        return $this->types;
    }
    
    public function getAsset(string $asset): ?string {
        return $this->assets[$asset] ?? null;
    }
    
    public function getAssets(string $dir = ""): array {
        return array_keys($this->assets);
    }
    
    public function getError(string $error): ?string {
        return $this->errors[$error] ?? null;
    }
    
    public function readAsset(string $asset): void {}
    public function runApi(): void {}

    static public function list(): array {
        return self::$list;
    }
}