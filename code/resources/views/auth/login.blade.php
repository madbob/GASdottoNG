@extends('app')

@section('content')

<div class="col-12 col-md-6 offset-md-3">
    @if($gas->message != '')
        <div class="alert alert-info">
            {!! nl2br($gas->message) !!}
        </div>
        <hr/>
    @endif

    @if($gas->restricted == '1')
        <div class="alert alert-warning">
            {{ _i('Modalità Manutenzione: Accesso Temporaneamente Ristretto ai soli Amministratori') }}
        </div>
        <hr/>
    @endif

    @if(!empty($gas->logo))
        <img class="img-responsive" src="{{ $gas->logo_url }}" alt="{{ $gas->name }}">
    @else
        <h2 class="text-center">{{ $gas->name }}</h2>
        <hr>
    @endif

    <br/>

    <?php $browser_name = strtolower((new Sinergi\BrowserDetector\Browser())->getName()) ?>
    @if ($browser_name != 'firefox' && $browser_name != 'chrome')
        <div class="alert alert-warning">
            {{ _i('GASdotto è testato con Firefox e Chrome/Chromium, ti consigliamo di usare uno di questi!') }}<br>
            <a href="https://www.mozilla.org/it/firefox/new/">{{ _i('Clicca qui per scaricare ed installare Firefox.') }}</a>
        </div>
        <br>
    @endif

    <form class="form-horizontal" method="POST" action="{{ route('login') }}">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">

        <div class="row mb-2">
            <label class="col-2 col-form-label">{{ _i('Username') }}</label>
            <div class="col-10">
                <input class="form-control" type="text" name="username" value="{{ old('username') }}">
            </div>
        </div>

        <div class="row mb-2">
            <label class="col-2 col-form-label">{{ _i('Password') }}</label>
            <div class="col-10">
                <input class="form-control" type="password" name="password">
            </div>
        </div>

        @if($gas->getConfig('language'))
            <input type="hidden" name="language" value="{{ $gas->getConfig('language') }}">
        @else
            <div class="row mb-2">
                <label class="col-2 col-form-label">{{ _i('Lingua') }}</label>
                <div class="col-10">
                    <select name="language">
                        @foreach(getLanguages() as $identifier => $lang)
                            <option value="{{ $identifier }}">{{ $lang }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        @endif

        <div class="row mb-2">
            <div class="col-10 offset-2">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="remember" class="remember-checkbox" data-attribute="remember_me" data-attribute-default="true" checked> {{ _i('Ricordami') }}
                    </label>
                </div>
            </div>
        </div>

        <br>

        <div class="row mb-2">
            <div class="col-10 offset-2">
                <button class="btn btn-success float-end" type="submit">{{ _i('Login') }}</button>
            </div>
        </div>
    </form>
</div>

<div class="col-12 col-md-6 offset-md-3">
    <hr/>
    <p>
        @if($gas->hasFeature('public_registrations'))
            <a href="{{ route('register') }}">{{ _i('Registrati') }}</a>
        @endif
        <a class="float-end" href="{{ route('password.request') }}">{{ _i('Recupero Password') }}</a>
    </p>
</div>

<nav class="fixed-bottom border-top p-3">
    <div class="container">
        <p>
            Powered by <a href="https://www.gasdotto.net/"><img src="{{ url('images/gasdotto.jpg') }}" style="height: 24px" alt="GASdotto"> GASdotto</a>.
        </p>
    </div>
</nav>

@endsection
