# Troubleshooting

* potrebbe essere necessario installare la localizzazione italiana del sistema (in particolare per formattare le date). Per installarla, qualora mancante, eseguire `dpkg-reconfigure locales` sul proprio server
* non è possibile inoltrare le email usando GMail, a causa delle restrizioni imposte all'autenticazione

# Scarica e Installa
GASdotto può essere installato localmente o in un container Docker utilizzando Laravel Sail.

Per installare GASdotto localmente, segui le istruzioni fornite sul sito ufficiale all'indirizzo [www.gasdotto.net/docs/installazione](https://www.gasdotto.net/docs/installazione).

Se preferisci installarlo in un container Docker, segui i seguenti passaggi.

## Installazione container Docker utilizzando Laravel Sail

##### Requisiti:

- Docker e Docker Compose

### Installazione

#### 1. Clona GASdottoNG e naviga nella cartella `<cartella di instaliazione>/GASdottoNG/code/`
```shell
git clone https://github.com/madbob/GASdottoNG.git && cd GASdottoNG/code/
```

#### 2. Installa le dipendenze con Composer tramite Docker
```shell
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php82-composer:latest \
    composer install --ignore-platform-reqs
```

#### 3. Crea il file .env e configuralo per Docker
```shell
cp .env.example .env
```
**Ricordati di configurare le impostazioni Docker nel file .env, abilitando o sostituendo i parametri commentati con quelli di default nella sezione 'For Docker' all'interno dello stesso file.*

#### 4. Avvia i container Docker ed entra nella Terminale all'interno del container del progetto.
*Il primo avvio potrebbe richiedere più o meno tempo a seconda delle caratteristiche del tuo computer e della connessione internet.*
```shell
sail up -d
```
```shell
sail shell
```

*Se non hai configurato un alias per `sail`, puoi utilizzare:*

```shell
./vendor/bin/sail up -d
```

```shell
./vendor/bin/sail shell
```

#### 5. All'interno del container:
Imposta la chiave APP_KEY

```shell
php artisan key:generate
```
Migrazioni e popolamento del database

```shell
php artisan migrate && php artisan db:seed && php artisan db:seed --class=FirstInstallSeed
```

#### 6. Installa i pacchetti npm necessari ed esegui Laravel Mix.
```shell
npm install && npm run development
```

Adesso puoi visitare `localhost` e iniziare a testare e sviluppare.

### Uscita

#### Per uscire dal container Docker, utilizza il comando `exit` nel terminale o semplicemente `CTRL+d`.
`exit`

#### Per fermare i container Docker, utilizza il comando `stop`.
`sail stop`

Per saperne di più su [Laravel Sail](https://laravel.com/docs/master/sail)
visita e consulta la documentazione ufficiale di Laravel.

### Uso comune nello sviluppo.
- `sail up -d` -> Per avviare i container Docker

- `sail shell` -> Per entrare nella terminale interattiva all'interno del container

#### Al interno, all'interno della terminale esegui i comandi necessari come se stessi lavorando in locale.

- `exit`-> Per uscire dal container
- `sail stop` -> Per fermare i container Docker
