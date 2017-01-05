#!/usr/bin/env bash

if [ -z "$1" ]; then
    FILTER=""
else
    FILTER="--filter $1"
fi

docker run \
	-t -i \
	--rm \
	-p 8000:8000 \
	-h dev.gasdotto.org \
	-v $(pwd):/app \
	-v $(pwd)/_docker/.docker_env:/app/.env \
	-v $(pwd)/.docker_bundle:/home/username/.composer \
	--name=dev-gasdotto-org \
	gasdotto/dev-gasdotto-org \
	vendor/phpunit/phpunit/phpunit $FILTER
