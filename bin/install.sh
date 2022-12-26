#!/bin/bash

##
# Installation PHP script executed via docker container
#
SOURCEDIR=$(dirname $(dirname $(realpath "$0")))
docker container run --rm --interactive -v ${SOURCEDIR}:/app/ php:8.1 php /app/bin/install.php
