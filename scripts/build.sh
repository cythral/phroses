#!/bin/bash
rm phroses.phar -f
rm phroses.tar.gz -f
rm phroses.tar -f

composer run-script build:js
composer run-script build:css

# copy build files and install dependencies
mkdir build
cp -r src build/src
cp composer.json build/src/composer.json
cp composer.lock build/src/composer.lock
cp .htaccess build/.htaccess
cp README.md build/README.md
cp LICENSE build/LICENSE
cp -r themes build/themes
cp -r plugins build/plugins
rm -rf build/themes/bloom2

cd build/src
composer install --no-dev --no-scripts

cd ../../
php scripts/phar.php

# copy phroses.phar to root for testing
cp build/phroses.phar phroses.phar 
chmod 775 phroses.phar

# cleanup
rm -rf build
rm -f phroses.tar

# make dist files for packagist
rm -rf dist
mkdir dist
cp phroses.tar.gz dist/phroses.tar.gz
cd dist
tar -zxvf phroses.tar.gz
rm phroses.tar.gz

echo -e "\e[42mBUILD COMPLETE\e[0m";