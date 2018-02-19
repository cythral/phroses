<?php

/**
 * This class provides a way for loading a theme from a folder
 */

namespace Phroses\Theme\Loaders;

use \Phroses\Theme\Loader;
use const \Phroses\{ INCLUDES };
use function \Phroses\{ getIncludeOutput, readfileCached, fileList };

class FolderLoader implements Loader {
    private $name;
    private $path;

    const DIR = INCLUDES["THEMES"];
    
    public function __construct(string $name, ?string $root = null) {
        $this->name = $name;
        $this->path = ($root ?? self::DIR)."/{$this->name}";
    }

    public function exists(): bool {
        return file_exists($this->path);
    }

    public function hasType(string $type): bool {
        return file_exists("{$this->path}/{$type}.tpl");
    }

    public function hasFolder(string $folder): bool {
        return file_exists("{$this->path}/{$folder}");
    }

    public function hasAsset(string $asset): bool {
        $asset = ltrim($asset, "/");
        return !empty($asset) && file_exists("{$this->path}/assets/{$asset}");
    }

    public function hasError(string $error): bool {
        $error = ltrim($error, "/");
        return file_exists("{$this->path}/errors/{$error}.php");
    }

    public function hasApi(): bool {
        return file_exists("{$this->path}/api.php");
    }

    public function getName(): string {
        return $this->name;
    }

    public function getPath(): string {
        return $this->path;
    }

    public function getType(string $type): ?string {
        return $this->hasType($type) ? file_get_contents("{$this->path}/{$type}.tpl") : null;
    }

    public function getTypes(): array {
        return array_map(function($type) {
            return pathinfo($type, PATHINFO_FILENAME);
        }, glob("{$this->path}/*.tpl"));
    }

    public function getAsset(string $asset): ?string {
        $asset = ltrim($asset, "/");
        return $this->hasAsset($asset) ? file_get_contents("{$this->path}/assets/{$asset}") : null;
    }

    public function getAssets(string $dir = ""): array {
        $dir = ltrim($dir, "/");
        
        return array_map(
            (function($asset) { return str_replace("{$this->path}/assets/", "", $asset); })->bindTo($this), 
            array_filter(fileList("{$this->path}/assets/{$dir}"), function($value) { return !is_dir($value); })
        );
    }

    public function getError(string $error): ?string {
        $error = ltrim($error, "/");
        return $this->hasError($error) ? getIncludeOutput("{$this->path}/errors/{$error}.php") : null;
    }

    public function readAsset(string $asset): void {
        $asset = ltrim($asset, "/");
        if($this->hasAsset($asset)) readfileCached("{$this->path}/assets/{$asset}");
    }

    public function runApi(): void {
        if($this->hasApi()) include "{$this->path}/api.php";
    }
}