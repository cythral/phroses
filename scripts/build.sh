#!/bin/bash
rm phroses.phar -f
rm phroses.tar.gz -f
rm phroses.tar -f

composer run build:js
composer run build:css

# create directories
mkdir build
mkdir -p build/var/phroses
mkdir -p build/usr/bin
mkdir -p build/etc/apache2/sites-available/
cp config/apache-vhost.conf build/etc/apache2/sites-available/phroses.conf
cp -r config/DEBIAN build/DEBIAN

# setup data directory
cp -r themes build/var/phroses/themes
cp -r plugins build/var/phroses/plugins
rm -rf build/var/phroses/themes/bloom2

# build phar
cp -r src build/src
cp composer.json build/src/composer.json
cp composer.lock build/src/composer.lock
cd build/src
composer install --no-dev --no-scripts
cd ../../
php scripts/phar.php
rm -rf build/src
mv build/phroses.phar build/usr/bin/phroses
chmod 775 build/usr/bin/phroses

# build .deb
printf "Version: $(echo $1 | sed 's/v//g')\n" >> build/DEBIAN/control
dpkg --build build

# copy phroses.phar to root for testing
cp build/usr/bin/phroses phroses.phar 
mv build.deb phroses-$1.deb
chmod 775 phroses.phar

# cleanup
rm -rf build
rm -f phroses.tar

echo -e "\e[42mBUILD COMPLETE\e[0m";