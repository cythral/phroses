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
cp composer.lock build/src/composer.lock
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

# build the phroses.phar and phroses.tar.gz files
cp build/phroses.phar phroses.phar 

# cleanup
rm -rf build
rm -f phroses.tar

echo -e "\e[42mBUILD COMPLETE\e[0m";

if [[ -f ".developer" ]]; then
    printf "Detected a .developer file.. do you want to remove it for testing? (Y/n): ";
    read answer;

    if [[ "${answer,,}" == "y" || "${answer,,}" == "" ]]; then
        rm .developer
    fi;
fi;


