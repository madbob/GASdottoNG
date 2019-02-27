#!/usr/bin/env bash

if [ -z "$1" ]; then
    FILTER=""
else
    FILTER="--filter $1"
fi

docker run \
	-t -i \
	--rm \
	-v $(pwd):/app \
	-v $(pwd)/_docker/.docker_env:/app/.env \
	-v $(pwd)/.docker_bundle:/home/username/.composer \
	gasdotto/dev-gasdotto-org \
	vendor/phpunit/phpunit/phpunit $FILTER
