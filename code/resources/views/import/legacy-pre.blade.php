@extends($theme_layout)

@section('content')

<div class="row">
    <div class="col-md-6 col-md-offset-3">
        @if(isset($error))
            <div class="alert alert-danger">
                {{ $error }}
            </div>

            <br/>
        @endif

        <div class="alert alert-info">
            <p>
                Da qui puoi importare i dati della tua vecchia istanza GASdotto. Indica il percorso completo dove GASdotto è installato, e la procedura automatica provvederà al resto. Potrebbe volerci un poco di tempo: non chiudere questa pagina!
            </p>
        </div>

        <br>

        <div class="alert alert-info">
            <p>
                Se hai accesso SSH al server, puoi anche eseguire manualmente la procedura eseguendo il comando<br>
                <pre>php artisan import:legacy /path/della/vecchia/istanza [mysql o pgsql] 127.0.0.1 vecchio_username vecchia_password vecchio_database</pre>
            </p>
        </div>

        <br>

        <div class="alert alert-warning">
            <p>
                <strong>Avvertenza</strong>: la password di tutti gli utenti sarà automaticamente resettata uguale al loro username.
                Ad esempio: per l'utente "mario" la nuova password sarà "mario".
                Le vecchie password non possono essere recuperate.
                Completata la procedura, raccomanda tutti i tuoi utenti di autenticarsi e cambiare password il prima possibile.
            </p>
        </div>

        <br/>

        <form class="form-horizontal" method="POST" action="{{ url('import/legacy') }}">
            {{ csrf_field() }}

            <div class="form-group">
                <label for="old_path" class="col-sm-{{ $labelsize }} control-label">Percorso</label>
                <div class="col-sm-{{ $fieldsize }}">
                    <input type="text" class="form-control" name="old_path" autocomplete="off">
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <button class="btn btn-success pull-right" type="submit">Importa</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
