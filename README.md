# GASdottoNG

GASdottoNG è l'evoluzione del progetto GASdotto, gestionale web-based per gruppi di acquisto.

Per riferimenti:

* Sito web: http://gasdotto.net/
* Vecchia implementazione (non più mantenuta): https://github.com/madbob/GASdotto

[![Build Status](https://travis-ci.org/madbob/GASdottoNG.svg?branch=master)](https://travis-ci.org/madbob/GASdottoNG)
[![Translations Status](https://hosted.weblate.org/widgets/gasdottong/-/translations/svg-badge.svg)](https://hosted.weblate.org/engage/gasdottong/?utm_source=widget)

### Installazione

Requisiti:
 * un web server
 * un database (testato con MariaDB, compatibile con MySQL, PostgreSQL)
 * PHP >= 7.2
 * composer

```bash
git clone https://github.com/madbob/GASdottoNG.git
cd GASdottoNG/code
composer install
# nel file .env vanno specificati i propri parametri di connessione al database e l'invio delle mail
cp .env.example .env
nano .env
```

Al primo accesso verranno eseguiti il popolamento del database e la configurazione iniziale, che permette anche l'importazione dei contenuti da una vecchia istanza di GASdotto.

Per aggiornare una istanza esistente:

```bash
cd GASdottoNG/
git pull
cd code/
php artisan migrate
composer update
composer install
```

### Configurazioni Avanzate

È possibile allestire una istanza di GASdottoNG che serva diversi GAS isolati tra loro, ciascuno con un proprio database. Per farlo, cambiare manualmente il valore true/false ritornato dalla funzione `global_multi_installation()` nel file `code/app/Helpers/Setup.php`.

Si presuppone che ogni istanza sia raggiungibile da istanza1.example.com, istanza2.example.com, istanza3.example.com... Ogni istanza deve avere un suo proprio file `.env`, nominato a seconda del dominio (`.env.istanza1`, `.env.istanza2`, `.env.istanza3`...).

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
 * è possibile installare l'applicazione in una sotto-cartella del proprio dominio, avendo cura di configurare il parametro `base_url` in `code/config/minify.config.php` con l'URL completo di path (escludendo però il riferimento a `index.php`)
 * per inoltrare le mail con GMail, è necessario abilitare l'[accesso alle applicazioni "meno sicure"](https://myaccount.google.com/lesssecureapps)

### Licenza

GASdotto è distribuito in licenza AGPLv3+.

Copyright (C) 2017/2018 Roberto Guido <bob@linux.it>
