# GASdottoNG

GASdottoNG intende essere l'evoluzione del progetto GASdotto, gestionale web-based per gruppi di acquisto.

Per riferimenti:

* http://gasdotto.net/
* https://github.com/madbob/GASdotto

### Inizializzazione

    // per installare Composer, package manager PHP
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
    // per clonare il repository ed inizializzare l'ambiente
    git clone https://github.com/madbob/GASdottoNG.git
    cd GASdottoNG/laravel
    composer update
    // nel file .env vanno specificati i propri parametri di connessione al database
    nano .env
    // per inizializzare il database
    php artisan migrate
