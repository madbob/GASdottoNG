# GASdottoNG

## WORK IN PROGRESS!!!

GASdottoNG intende essere l'evoluzione del progetto GASdotto, gestionale web-based per gruppi di acquisto.

Per riferimenti:

* http://gasdotto.net/
* https://github.com/madbob/GASdotto

### Build status: [![Build Status](https://travis-ci.org/madbob/GASdottoNG.svg?branch=master)](https://travis-ci.org/madbob/GASdottoNG)

### Installazione

```bash
# per installare Composer, package manager PHP
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
# per clonare il repository ed inizializzare l'ambiente
git clone https://github.com/madbob/GASdottoNG.git
cd GASdottoNG/code
# per installare le dipendenze PHP
composer install
# nel file .env vanno specificati i propri parametri di connessione al database
cp .env.example .env
nano .env
# per inizializzare il database
php artisan key:generate
php artisan migrate --seed
php artisan db:seed --class=DemoSeeder
php artisan db:seed --class=MovementTypesSeeder
```

Viene creato un utente amministratore di default con username `root` e password `root`.

### Inizializzazione

È possibile importare i contenuti da una istanza GASdotto Legacy esistente con il comando:

```
php artisan import:legacy {old_driver} {old_host} {old_username} {old_password} {old_database} {new_driver} {new_host} {new_username} {new_password} {new_database}
```

ad esempio

```
php artisan import:legacy pgsql localhost gasdotto pippo gasdotto mysql localhost gasdotto pippo gasdottong
```

### Docker

```bash
cd code
./build.sh
```

```bash
cd code
./run.sh #quindi collegarsi a http://localhost:8000
./test.sh #per eseguire i test automatici
./test.sh PATTERN_NOME_TEST #per eseguire i test il cui nome matcha il pattern
```

### Troubleshooting

 * l'applicazione utilizza di default la localizzazione italiana del sistema (in particolare per formattare le date). Per installarla, qualora mancante, eseguire `dpkg-reconfigure locales` sul proprio server

