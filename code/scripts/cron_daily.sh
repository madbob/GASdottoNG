#!/bin/sh

# Dedicato a gasdotto.net
#
# Script da invocare in cron, una volta al giorno, per eseguire i task
# automatici su tutte le istanze.
# I comandi qui enumerati devono essere gli stessi che appaiono in
# app/Console/Kernel.php per la frequenza "daily"

cd ..
mv .env /tmp

for i in `ls .env.*`
do
        echo $i
        cp $i .env

        php artisan check:fees
        php artisan close:orders
        php artisan open:orders
        php artisan remind:orders
done

mv /tmp/.env .
