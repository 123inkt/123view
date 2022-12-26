#!/bin/bash

SOURCEDIR=$(dirname $(dirname $(realpath "$0")))

echo ""
echo "  _ ____  _____      _                 _           _        _ _"
echo " / |___ \|___ /_   _(_) _____      __ (_)_ __  ___| |_ __ _| | | ___ _ __"
echo " | | __) | |_ \ \ / / |/ _ \ \ /\ / / | | '_ \/ __| __/ _\` | | |/ _ \ '__|"
echo " | |/ __/ ___) \ V /| |  __/\ V  V /  | | | | \__ \ || (_| | | |  __/ |"
echo " |_|_____|____/ \_/ |_|\___| \_/\_/   |_|_| |_|___/\__\__,_|_|_|\___|_|"



##
# Installation PHP script executed via docker container
#
docker container run --rm --interactive -v ${SOURCEDIR}:/app php:8.1 php /app/bin/install.php --hostname ${HOSTNAME} --sourcedir ${SOURCEDIR}
