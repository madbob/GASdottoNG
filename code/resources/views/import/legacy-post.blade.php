@extends($theme_layout)

@section('content')

<div class="row">
    <div class="col-md-6 col-md-offset-3">
        <p>
            La procedura è stata completata!
        </p>
        <p>
            Qui di seguito, l'output del comando: qualche elemento potrebbe non essere stato importato correttamente.
        </p>

        <pre>
            {!! nl2br($output->fetch()) !!}
        </pre>

        <p class="text-center">
            <a class="btn btn-default" href="{{ url('/') }}">Torna al login. Ricorda: la tua nuova password è uguale allo username!</a>
        </p>
    </div>
</div>

@endsection
