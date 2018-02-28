#!/bin/bash
rm phroses.phar -f
rm phroses.tar.gz -f
rm phroses.tar -f

npm install
npm run build:css
npm run build:js

# copy build files and install dependencies
mkdir build
cp -r src build/src
cp composer.json build/src/composer.json
cp .htaccess build/.htaccess
cp README.md build/README.md
cp LICENSE build/LICENSE
cp -r themes build/themes
cp -r plugins build/plugins
rm -rf build/themes/bloom2


cd build/src
composer update --no-dev

cd ../../
php scripts/phar.php
cp build/phroses.phar phroses.phar
rm -rf build



