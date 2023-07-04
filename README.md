# GASdotto

GASdotto è il gestionale web-based per gruppi di acquisto.

[![Build Status](https://github.com/madbob/gasdottong/actions/workflows/test.yml/badge.svg)](https://github.com/madbob/GASdottoNG/actions)
[![Maintainability](https://api.codeclimate.com/v1/badges/1ff2c4db03668abadd46/maintainability)](https://codeclimate.com/github/madbob/GASdottoNG/maintainability)
[![Translations Status](https://hosted.weblate.org/widgets/gasdottong/-/translations/svg-badge.svg)](https://hosted.weblate.org/engage/gasdottong/?utm_source=widget)

### Per documentazione e hosting gratuito visita il sito www.gasdotto.net

### Troubleshooting

* potrebbe essere necessario installare la localizzazione italiana del sistema (in particolare per formattare le date). Per installarla, qualora mancante, eseguire `dpkg-reconfigure locales` sul proprio server
* non è possibile inoltrare le email usando GMail, a causa delle restrizioni imposte all'autenticazione

# Scarica e Installa
GASdotto può essere installato localmente o in un container Docker tramite Laravel Sail. 

Per installare GASdotto localmente, segui le istruzioni fornite sul sito ufficiale all'indirizzo [www.gasdotto.net/docs/installazione](https://www.gasdotto.net/docs/installazione).

Se preferisci installarlo in un container Docker, segui i seguenti passaggi.

## Installazione container Docker tramite Laravel Sail

##### Requisiti:
- php (comprese le estensioni necessarie per far funzionare Laravel)

- composer

- Docker and Docker Compose

### Installazione 

#### 1. Clona GASdottoNG e naviga nella cartella `<cartella di instaliazione>/GASdottoNG/code/`
`git clone https://github.com/madbob/GASdottoNG.git && cd GASdottoNG/code/`
 
#### 2. Installa le dipendenze del progetto dal file *composer.lock*.
`composer install`

#### 3. Crea il file *.env* e imposta la chiave *APP_KEY*.
`cp .env.example .env`

`php artisan key:generate`

#### 4. Avvia i contenitori Docker ed entra nella Terminale all'interno del container del progetto.
`sail up -d`

`sail shell`

*Se non hai configurato un alias per `sail`, puoi utilizzare:*

`./vendor/bin/sail up -d`

`./vendor/bin/sail shell`

#### 5. All'interno del container, esegui i comandi per le migrazioni e il popolamento del database.
`php artisan migrate`

`php artisan db:seed`

`php artisan db:seed --class=FirstInstallSeed`

#### 6. Installa i pacchetti npm necessari ed esegui Laravel Mix.
`npm install`

`npm run development`

### Uscita

#### Per uscire dal container Docker, utilizza il comando `exit` nel terminale o semplicemente `CTRL+d`.
`exit`

#### Per fermare i contenitori Docker, utilizza il comando `stop`.
`sail stop`

Per saperne di più su [Laravel Sail](https://laravel.com/docs/master/sail) 
visita e consulta la documentazione ufficiale di Laravel.

### Uso comune nello sviluppo.
- `sail up -d` -> Per avviare i contenitori Docker 

- `sail shell` -> Per entrare nella terminale interattiva all'interno del container

#### Al interno, all'interno della terminale esegui i comandi necessari come se stessi lavorando in locale.

- `exit`-> Per uscire dalla container
- `sail stop` -> Per fermare i contenitori Docker 

### Licenza

GASdotto è distribuito in licenza AGPLv3+.

Copyright (C) 2017/2023 Roberto Guido <bob@linux.it>