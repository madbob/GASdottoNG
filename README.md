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

### Configurazioni Avanzate

È possibile allestire una istanza di GASdottoNG che serva diversi GAS isolati tra loro, ciascuno con un proprio database. Per farlo, cambiare manualmente il valore true/false ritornato dalla funzione `global_multi_installation()` nel file `code/app/Helpers/Setup.php`.

Si presuppone che ogni istanza sia raggiungibile da istanza1.example.com, istanza2.example.com, istanza3.example.com... Ogni istanza deve avere un suo proprio file `.env`, nominato a seconda del dominio (`.env.istanza1`, `.env.istanza2`, `.env.istanza3`...).

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
 * per versioni di MySQL inferiori alla 5.7, occorre editare il file `code/app/Providers/AppServiceProvider.php`. Fare riferimento a [queste indicazioni](https://laravel-news.com/laravel-5-4-key-too-long-error).

### Licenza

GASdotto è distribuito in licenza AGPLv3+.

Copyright (C) 2017 Roberto Guido <bob@linux.it>
