#!/bin/bash

eval "$(ssh-agent -s)"
chmod 600 config/deploy.key
ssh-add config/deploy.key

if [ ! -z "$TRAVIS_TAG" ] && [ "$TRAVIS_PULL_REQUEST" == "false" ]; then
    version=$TRAVIS_BRANCH
    repo="stable"
else
    version="${TRAVIS_BRANCH}+${TRAVIS_BUILD_NUMBER}"
    repo="unstable"
fi

composer run build -- $version
scp phroses-${version}.deb travis@deb.cythral.com:phroses-${version}.deb

ssh -4 travis@deb.cythral.com <<PHRS
    aptly repo add ${repo} phroses-${version}.deb
    aptly publish update stretch
    rm phroses-${version}.deb
    ./publish.sh Phroses ${version} ${repo}
PHRS