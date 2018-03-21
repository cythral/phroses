#!/bin/bash

eval "$(ssh-agent -s)"
chmod 600 config/deploy.key
ssh-add config/deploy.key

if [ ! -z "$TRAVIS_TAG" ] && [ "$TRAVIS_PULL_REQUEST" == "false" ]; then

ssh travis@deb.cythral.com <<PHRS
    cd phroses 
    composer install
    export PATH=\$PATH:\$PWD/vendor/bin:\$(npm bin)
    git pull origin $TRAVIS_BRANCH; 
    composer run build -- $TRAVIS_BRANCH
    aptly repo add stable phroses-$TRAVIS_BRANCH.deb
    aptly publish update stretch stable
    git clean -xdf
PHRS

else
    
ssh travis@deb.cythral.com <<PHRS
    cd phroses
    composer install
    export PATH=\$PATH:\$PWD/vendor/bin:\$(npm bin)
    git pull origin $TRAVIS_BRANCH
    composer run build -- "$TRAVIS_BRANCHa$TRAVIS_BUILD_NUMBER"
    aptly repo add unstable phroses-$TRAVIS_BRANCHa$TRAVIS_BUILD_NUMBER
    aptly publish update stretch unstable
    git clean -xdf
PHRS

fi