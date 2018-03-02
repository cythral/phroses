<?php

namespace Phroses\Composer;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;

class PhrosesInstallerPlugin implements PluginInterface {
    public function activate(Composer $composer, IOInterface $io) {
        $installer = new PhrosesInstaller($io, $composer);
        $composer->getInstallationManager()->addInstaller($installer);
    }
}