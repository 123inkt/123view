#!/bin/bash

set -e

BINDIR=$(dirname $(realpath "$0"))
SOURCEDIR=$(dirname ${BINDIR})
REBUILD='no'
MODE=''
FOLLOW_LOG='1'

# Map arguments
while [[ $# -gt 0 ]] && [[ "$1" == "-"* ]] ;
do
    opt="$1";
    shift;
    case "$opt" in
        "-b" )
           REBUILD='yes';;
        "--prod" )
           MODE='prod';;
        "--dev" )
           MODE='dev';;
        "--skip-log" )
           FOLLOW_LOG='0';;
        *) echo >&2 "Invalid option: $@"; exit 1;;
   esac
done

if [ "$MODE" == '' ]; then
    echo -n "Deployment mode: [prod/dev] "
    read MODE

    if [ "$MODE" != 'prod' ] && [ "$MODE" != 'dev' ]; then
        echo "Invalid mode: ${MODE}"
        exit 1;
    fi
fi

echo "[MODE]: ${MODE}"

if [ "$REBUILD" == 'yes' ]; then
    echo "[REBUILD]: yes"
else
    echo "[REBUILD]: no.  Use '-b' flag to force docker image rebuild."
fi

##
# Stop current containers
#
docker compose stop

##
# remove cache directory
#
rm -rf "${SOURCEDIR}/var/cache"

##
# Start new container
#
if [ "$MODE" == 'prod' ]; then
    set -o allexport
    source .env
    [[ -f ".env.prod" ]] && source .env.prod
    [[ -f ".env.prod.local" ]] && source .env.prod.local
    set +o allexport

    cp ${SSL_DHPARAM}         ./docker/ssl/production/dhparam.pem
    cp ${SSL_CERTIFICATE}     ./docker/ssl/production/production.crt
    cp ${SSL_CERTIFICATE_KEY} ./docker/ssl/production/production.key

    if [ "$REBUILD" == 'yes' ]; then
        DOCKER_BUILDKIT=1 docker compose -f docker-compose.yml -f docker-compose.production.yml build
    fi
    docker compose -f docker-compose.yml -f docker-compose.production.yml up -d

    if [ "$FOLLOW_LOG" == '1' ]; then
        docker compose logs --tail=5 --follow
    fi

    exit 0;

elif [ "$MODE" == 'dev' ]; then
    set -o allexport
    source .env
    [[ -f ".env.dev" ]] && source .env.dev
    [[ -f ".env.dev.local" ]] && source .env.dev.local
    set +o allexport

    if [ "$REBUILD" == 'yes' ]; then
        DOCKER_BUILDKIT=1 docker compose build
    fi
    docker compose up -d --remove-orphans

    if [ "$FOLLOW_LOG" == '1' ]; then
        docker compose logs --tail=5 --follow
    fi
fi
