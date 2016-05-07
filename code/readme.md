## GASdottoNG

## WORK IN PROGRESS!!!

## Installazione

```
git clone https://github.com/madbob/GASdottoNG.git
cd GASdottoNG/code
composer install
cp .env.example .env
[editare .env coi propri parametri per il database]
php artisan migrate
php artisan db:seed
php artisan key:generate
```

## Inizializzazione

È possibile importare i contenuti da una istanza GASdotto Legacy esistente con il comando:

```
php artisan import:legacy {old_driver} {old_host} {old_username} {old_password} {old_database} {new_driver} {new_host} {new_username} {new_password} {new_database}
```

ad esempio

```
php artisan import:legacy pgsql localhost gasdotto pippo gasdotto mysql localhost gasdotto pippo gasdottong
```

## Troubleshooting

 * l'applicazione utilizza di default la localizzazione italiana del sistema (in particolare per formattare le date). Per installarla, qualora mancante, eseguire `dpkg-reconfigure locales` sul proprio server
