# GASdottoNG

GASdottoNG intende essere l'evoluzione del progetto GASdotto, gestionale web-based per gruppi di acquisto.

Per riferimenti:

* http://gasdotto.net/
* https://github.com/madbob/GASdotto

### Build status: [![Build Status](https://travis-ci.org/madbob/GASdottoNG.svg?branch=master)](https://travis-ci.org/madbob/GASdottoNG)

### Inizializzazione manuale

```bash
# per installare Composer, package manager PHP
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
# per clonare il repository ed inizializzare l'ambiente
git clone https://github.com/madbob/GASdottoNG.git
cd GASdottoNG/code
# per installare le dipendenze PHP
composer update --no-scripts
# nel file .env vanno specificati i propri parametri di connessione al database
cp .env.example .env
nano .env
# per inizializzare il database
php artisan migrate --seed
php artisan db:seed --class=DemoSeeder
```

### Inizializzazione con docker

```bash
cd code
./build.sh
```
    
### Sviluppo con docker

```bash
cd code
./run.sh #quindi collegarsi a http://localhost:8000
./test.sh #per eseguire i test automatici
./test.sh PATTERN_NOME_TEST #per eseguire i test il cui nome matcha il pattern
```
