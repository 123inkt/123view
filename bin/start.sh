#!/bin/bash

set -e

BINDIR=$(dirname $(realpath "$0"))
SOURCEDIR=$(dirname ${BINDIR})
REBUILD='no'

# Map arguments
while getopts b flag
do
    case "${flag}" in
        b) REBUILD='yes';;
    esac
done

echo -n "Deployment mode: [prod/dev] "
read mode

if [ "$mode" != 'prod' ] && [ "$mode" != 'dev' ]; then
    echo "Invalid mode: ${mode}"
    exit 1;
fi

if [ "$mode" == 'dev' ]; then
    if [ "$REBUILD" == 'yes' ]; then
        echo "[REBUILD]: yes"
    else
        echo "[REBUILD]: no.  Use '-b' flag to force docker image rebuild."
    fi
fi

##
# Stop current containers
#
docker-compose down

##
# setup network
#
docker network rm commit-notification-network || true
docker network create --driver bridge commit-notification-network || true

##
# remove cache directory
#
rm -rf ${SOURCEDIR}/var/cache

##
# Start new container
#
if [ "$mode" == 'prod' ]; then
    set -o allexport
    source .env
    [[ -f ".env.prod" ]] && source .env.prod
    [[ -f ".env.prod.local" ]] && source .env.prod.local
    set +o allexport

    DOCKER_BUILDKIT=1 docker-compose -f docker-compose.yml -f docker-compose.production.yml build
    docker-compose -f docker-compose.yml -f docker-compose.production.yml up -d

    echo ""
    echo "  Run 'docker-compose logs --tail=2 --follow' to follow the logs"
    echo ""

    exit 0;

elif [ "$mode" == 'dev' ]; then
    set -o allexport
    source .env
    [[ -f ".env.dev" ]] && source .env.dev
    [[ -f ".env.dev.local" ]] && source .env.dev.local
    set +o allexport

    if [ "$REBUILD" == 'yes' ]; then
        DOCKER_BUILDKIT=1 docker-compose build
    fi
    docker-compose up -d
    docker-compose logs --tail=2 --follow
fi
