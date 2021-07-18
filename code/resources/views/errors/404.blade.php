<!DOCTYPE html>
<html lang="{{ htmlLang() }}">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">

        <title>GASdotto: ERRORE</title>

        <link rel="stylesheet" type="text/css" href="{{ mix('/css/gasdotto.css') }}">
    </head>
    <body>
        <x-larastrap::navbar title="GASdotto" />

        <div class="container">
            <div class="row">
                <div class="col-md-12" id="main-contents">
                    <br><br><br><br>
                    <h1>Questa pagina non esiste...</h1>
                    <br><br>
                    <p>
                        <a class="btn btn-light btn-lg" href="{{ route('dashboard') }}">Torna alla Home</a>
                    </p>
                </div>
            </div>
        </div>
    </body>
</html>
