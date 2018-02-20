<?php

namespace Phroses\Theme;

interface Loader {
    public function exists(): bool;
    public function hasType(string $type): bool;
    public function hasFolder(string $folder): bool;
    public function hasAsset(string $asset): bool;
    public function hasError(string $error): bool;
    public function hasApi(): bool;
    public function getName(): string;
    public function getType(string $type): ?string;
    public function getTypes(): array;
    public function getAsset(string $asset): ?string;
    public function getAssets(string $dir = ""): array;
    public function getError(string $error): ?string;
    public function readAsset(string $asset): void;
    public function runApi(): void;
}