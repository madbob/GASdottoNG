#!/bin/sh

# Dedicato a gasdotto.net
#
# Script da invocare in cron, una volta al mese, per eseguire i task
# automatici su tutte le istanze.
# I comandi qui enumerati devono essere gli stessi che appaiono in
# app/Console/Kernel.php per la frequenza "monthly"

php artisan clean:caches

chown -R www-data:www-data storage/logs/*
