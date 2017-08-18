# GASdottoNG

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
```

Al primo accesso verranno eseguiti il popolamento del database e la configurazione iniziale, che permette anche l'importazione dei contenuti da una vecchia istanza di GASdotto.

### Docker

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

 * l'applicazione utilizza di default la localizzazione italiana del sistema (in particolare per formattare le date). Per installarla, qualora mancante, eseguire `dpkg-reconfigure locales` sul proprio server

### Licenza

GASdotto è distribuito in licenza AGPLv3+.

Copyright (C) 2017 Roberto Guido <bob@linux.it>
