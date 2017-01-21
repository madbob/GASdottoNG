#!/usr/bin/env bash

set -x
set -e

echo ===Building docker image...===
cd _docker
docker build --build-arg USERID=$(id -u) -t gasdotto/dev-gasdotto-org .
cd -

cat <<EOF > _temp_script_.sh
#!/bin/bash
set -x
set -e

composer update --no-scripts

touch /app/database/database.sqlite

php artisan migrate --seed
php artisan db:seed --class=DemoSeeder

EOF

chmod +x _temp_script_.sh

docker run \
	-t -i \
	--rm \
	-h dev.gasdotto.org \
	-v $(pwd):/app \
	-v $(pwd)/_docker/.docker_env:/app/.env \
	-v $(pwd)/.docker_bundle:/home/username/.composer \
	--name=dev-gasdotto-org \
	gasdotto/dev-gasdotto-org \
	./_temp_script_.sh

rm -f _temp_script_.sh
