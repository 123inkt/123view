#!/bin/bash

SOURCEDIR=$(dirname $(dirname $(realpath "$0")))

# ask which ssl option should be used
echo ""
echo "[1] Get me started with self-signed certificates, i'll replace this later"
echo "[2] Setup with my own ssl certificates"
echo ""
echo -e "Use self-signed ssl certificate or use my own? "
read choice

if [ "${choice}" == "1" ]; then
    cert="self-signed"
elif [ "${choice}" == "2" ]; then
    cert="provided"
else
    exit
fi

##
# Installation PHP script executed via docker container
#
docker container run --rm --interactive -v ${SOURCEDIR}:/app php:8.1 php /app/bin/install.php --ssl ${cert}
