<!DOCTYPE html>
<html lang="{{ htmlLang() }}">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">

        <title>GASdotto: ERRORE</title>

        <link rel="stylesheet" type="text/css" href="{{ url('/css/bootstrap.min.css') }}">
    </head>
    <body>
        <nav class="navbar navbar-default navbar-fixed-top navbar-inverse">
            <div class="container-fluid">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand hidden-md" href="{{ url('/') }}">GASdotto</a>
                </div>
            </div>
        </nav>

        <div class="container">
            <div class="row">
                <div class="col-md-12" id="main-contents">
                    <br><br><br><br>
                    <h1>Oops... Si è verificato un errore...</h1>
                    <br><br>
                    <p>
                        Gli errori vengono solitamente intercettati e notificati agli sviluppatori.
                    </p>
                    <p>
                        Se questo dovesse continuare a ripetersi, segnalalo all'indirizzo info@gasdotto.net avendo cura di specificare:
                    </p>
                    <ul>
                        <li>l'istanza su cui stavi lavorando</li>
                        <li>cosa stavi facendo nel momento in cui si è manifestato</li>
                        <li>quale utente, fornitore, ordine o prenotazione stavi manipolando</li>
                    </ul>
                    <br><br>
                    <p>
                        GASdotto è in continua evoluzione... Ma di tanto in tanto ci scappa qualche svista!
                    </p>
                    <br><br>
                    <p>
                        <a class="btn btn-default btn-lg" href="{{ url('/') }}">Torna alla Home</a>
                    </p>
                </div>
            </div>
        </div>
    </body>
</html>
