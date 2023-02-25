# GASdotto

GASdotto è il gestionale web-based per gruppi di acquisto.

[![Build Status](https://github.com/madbob/gasdottong/actions/workflows/test.yml/badge.svg)](https://github.com/madbob/GASdottoNG/actions)
[![Maintainability](https://api.codeclimate.com/v1/badges/1ff2c4db03668abadd46/maintainability)](https://codeclimate.com/github/madbob/GASdottoNG/maintainability)
[![Translations Status](https://hosted.weblate.org/widgets/gasdottong/-/translations/svg-badge.svg)](https://hosted.weblate.org/engage/gasdottong/?utm_source=widget)

### Per documentazione e hosting gratuito visita il sito www.gasdotto.net

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
 * non è possibile inoltrare le email usando GMail, a causa delle restrizioni imposte all'autenticazione

### Licenza

GASdotto è distribuito in licenza AGPLv3+.

Copyright (C) 2017/2023 Roberto Guido <bob@linux.it>
