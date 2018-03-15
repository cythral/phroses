<?php

namespace Phroses\Plugins;

use \Exception;
use const \Phroses\INCLUDES;

class Plugin {
    use \Phroses\Traits\Properties;

    const ROOT = INCLUDES["PLUGINS"];
    const DEFAULT_CONFIG = "/config.json";
    const DEFAULT_BOOTSTRAP = "/bootstrap.php";

    public function __construct(string $name) {
        $this->name = $name;
        $this->config = new Config($this->configFile);
        $this->bootstrap();
    }

    protected function getRoot() {
        return self::ROOT."/{$this->name}";
    }

    protected function getConfigFile() {
        return "{$this->root}".self::DEFAULT_CONFIG;
    }

    protected function getBootstrapFile() {
        return $this->config->bootstrap ?? "{$this->root}".self::DEFAULT_BOOTSTRAP;
    }

    protected function bootstrap() {
        if(!file_exists($this->bootstrapFile)) {
            throw new Exception("Bootstrap file not found for plugin {$this->name}");
        }

        include $this->bootstrapFile;
    }

    static public function list() {
        return array_map(function($value) {
            return basename($value);
        }, glob(self::ROOT."/*", GLOB_ONLYDIR));
    }

    static public function loadAll() {
        $plugins = [];

        foreach(self::list() as $pluginName) {
            $plugin = new self($pluginName);
            $plugins[$plugin->name] = $plugin;
        }

        return $plugins;
    }
}