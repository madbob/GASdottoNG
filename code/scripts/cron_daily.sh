#!/bin/sh

# Script da invocare in cron, una volta ogni ora, per eseguire i task automatici su tutte le istanze

cd ..
mv .env /tmp

for i in `ls .env.*`
do
        echo $i
        cp $i .env

        php artisan schedule:run
done

mv /tmp/.env .
