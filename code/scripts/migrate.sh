#!/bin/sh

# Usato per fare le migrazioni su tutte le istanze ed aggiornare il DB ad ogni 
# deploy

cd ..
mv .env /tmp

for i in `ls .env.*`
do
        echo $i
        cp $i .env

        php artisan migrate
        php artisan fix:database
done

mv /tmp/.env .
