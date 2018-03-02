<?php

namespace Phroses\Composer;

use Composer\Package\PackageInterface;
use Composer\Installer\LibraryInstaller;

class PhrosesInstaller extends LibraryInstaller {

    /**
     * {@inheritDoc}
     */
    public function getInstallPath(PackageInterface $package) {
        if($package->getPrettyName() !== "cythral/phroses") {
            throw new \InvalidArgumentException(
                "This installer only works for cythral/phroses"
            );
        }

        return '';
    }

    /**
     * {@inheritDoc}
     */
    public function supports($packageType) {
        return strtolower($packageType) == "package";
    }
}