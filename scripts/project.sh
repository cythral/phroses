#!/bin/bash

if [[ $COMPOSER_DEV_MODE == 0 ]]; then
    mv dist/phroses.tar.gz /tmp/phroses.tar.gz
    rm -rf $PWD/*
    mv /tmp/phroses.tar.gz phroses.tar.gz
    tar -zxvf phroses.tar.gz > /dev/null
    rm phroses.tar.gz
fi;