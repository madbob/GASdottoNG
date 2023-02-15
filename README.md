# GASdotto

GASdotto è il gestionale web-based per gruppi di acquisto.

[![Build Status](https://github.com/madbob/gasdottong/actions/workflows/test.yml/badge.svg)](https://github.com/madbob/GASdottoNG/actions)
[![Maintainability](https://api.codeclimate.com/v1/badges/1ff2c4db03668abadd46/maintainability)](https://codeclimate.com/github/madbob/GASdottoNG/maintainability)
[![Translations Status](https://hosted.weblate.org/widgets/gasdottong/-/translations/svg-badge.svg)](https://hosted.weblate.org/engage/gasdottong/?utm_source=widget)

### Per documentazione e hosting gratuito visita il sito www.gasdotto.net

### Installazione

Requisiti:
 * un web server
 * un database (testato con MariaDB, compatibile con MySQL, PostgreSQL)
 * PHP >= 8.1
 * composer

```bash
git clone https://github.com/madbob/GASdottoNG.git
cd GASdottoNG/code
composer install
cp .env.example .env
php artisan key:generate

# nel file .env vanno specificati i propri parametri di connessione al database e l'invio delle mail
nano .env

php artisan migrate
php artisan db:seed
php artisan db:seed --class=FirstInstallSeed
```

Per aggiornare una istanza esistente:

```bash
cd GASdottoNG/
git pull
cd code/
php artisan migrate
php artisan fix:database
composer update
composer install
```

### Docker

Per chi lo trovasse più comodo, è previsto uno script per costruirsi un container Docker in cui procedere con lo sviluppo.

```bash
cd code
./build.sh
```

```bash
cd code
./run.sh # quindi collegarsi a http://localhost:8000
./test.sh # per eseguire i test automatici
./test.sh PATTERN_NOME_TEST # per eseguire i test il cui nome matcha il pattern
```

### Troubleshooting

 * potrebbe essere necessario installare la localizzazione italiana del sistema (in particolare per formattare le date). Per installarla, qualora mancante, eseguire `dpkg-reconfigure locales` sul proprio server
 * per versioni di MySQL inferiori alla 5.7, occorre editare il file `code/app/Providers/AppServiceProvider.php`. Fare riferimento a [queste indicazioni](https://laravel-news.com/laravel-5-4-key-too-long-error).
 * per inoltrare le mail con GMail, è necessario abilitare l'[accesso alle applicazioni "meno sicure"](https://myaccount.google.com/lesssecureapps)

### Licenza

GASdotto è distribuito in licenza AGPLv3+.

Copyright (C) 2017/2022 Roberto Guido <bob@linux.it>
