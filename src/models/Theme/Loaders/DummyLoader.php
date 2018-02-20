<?php

namespace Phroses\Theme\Loaders;

use \Phroses\Theme\Loader;

class DummyLoader implements Loader {
    public $types = [];
    public $errors = [];
    public $assets = [];
    public $folders = [];
    public $exists = true;
    public $api = false;
    
    public function __construct(string $name) {
        $this->name = $name;
        $this->path = "";
    }
    
    public function exists(): bool {
        return true;
    }
    
    public function hasType(string $type): bool {
        return array_key_exists($type, $this->types);
    }
    
    public function hasFolder(string $folder): bool {
        return (bool) array_search($folder, $this->folders);    
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
    
    public function getName(): string;
    public function getPath(): string;
    
    public function getType(string $type): ?string;
    
    public function getTypes(): array;
    
    public function getAsset(string $asset): ?string {
        
    }
    
    public function getAssets(string $dir = ""): array {
        return $this->assets;
    }
    
    public function getError(string $error): ?string {
        return $this->errors[$error];
    }
    
    public function readAsset(string $asset): void {}
    public function runApi(): void {}
}