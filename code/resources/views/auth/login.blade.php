@extends($theme_layout)

@section('content')

<div class="col-md-6 col-md-offset-3">
    @if($gas->message != '')
        <div class="alert alert-info">
            {!! nl2br($gas->message) !!}
        </div>
        <hr/>
    @endif

    @if($gas->restricted == '1')
        <div class="alert alert-warning">
            Modalità Manutenzione: Accesso Temporaneamente Ristretto ai soli Amministratori
        </div>
        <hr/>
    @endif

    @if(!empty($gas->logo))
        <img class="img-responsive" src="{{ $gas->logo_url }}">
        <br/>
    @endif

    <?php $browser_name = strtolower((new Sinergi\BrowserDetector\Browser())->getName()) ?>
    @if ($browser_name != 'firefox' && $browser_name != 'chrome')
        <div class="alert alert-warning">
            GASdotto è testato con Firefox e Chrome/Chromium, ti consigliamo di usare uno di questi!<br>
            <a href="https://www.mozilla.org/it/firefox/new/">Clicca qui per scaricare ed installare Firefox.</a>
        </div>
        <br>
    @endif

    <form class="form-horizontal" method="POST" action="{{ url('login') }}">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">

        <div class="form-group">
            <label class="col-sm-2 control-label">Username</label>
            <div class="col-sm-10">
                <input class="form-control" type="text" name="username" value="{{ old('username') }}">
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label">Password</label>
            <div class="col-sm-10">
                <input class="form-control" type="password" name="password">
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="remember"> Ricordami
                    </label>
                </div>
            </div>
        </div>

        <br>

        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <button class="btn btn-success pull-right" type="submit">Login</button>
            </div>
        </div>
    </form>
</div>

@if($gas->has_mail())
    <div class="col-md-6 col-md-offset-3">
        <hr/>
        <p>
            <a class="pull-right" href="{{ url('password/reset') }}">Recupero Password</a>
        </p>
    </div>
@endif

@endsection
