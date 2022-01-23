@extends('app')

@section('content')

<div class="col-12 col-md-6 offset-md-3 mb-5">
    @if($gas->message != '')
        <div class="alert alert-info">
            {!! nl2br($gas->message) !!}
        </div>
        <hr/>
    @endif

    @if($gas->restricted == '1')
        <div class="alert alert-warning text-center mt-3">
            {{ _i('Modalità Manutenzione: Accesso Temporaneamente Ristretto ai soli Amministratori') }}
        </div>
        <hr/>
    @endif

    @if(!empty($gas->logo))
        <p class="text-center mt-3">
            <img class="img-fluid" src="{{ $gas->logo_url }}" alt="{{ $gas->name }}">
        </p>
    @else
        <h2 class="text-center mt-3">{{ $gas->name }}</h2>
    @endif

    <hr>

    <br/>

    <?php $browser_name = strtolower((new Sinergi\BrowserDetector\Browser())->getName()) ?>
    @if ($browser_name != 'firefox' && $browser_name != 'chrome')
        <div class="alert alert-warning">
            {{ _i('GASdotto è testato con Firefox e Chrome/Chromium, ti consigliamo di usare uno di questi!') }}<br>
            <a href="https://www.mozilla.org/it/firefox/new/">{{ _i('Clicca qui per scaricare ed installare Firefox.') }}</a>
        </div>
        <br>
    @endif

    <x-larastrap::form method="POST" action="{{ route('login') }}" :buttons="[['color' => 'success', 'label' => _i('Login'), 'type' => 'submit']]">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="language" value="{{ $gas->getConfig('language') }}">

        <x-larastrap::username name="username" :label="_i('Username')" />
        <x-larastrap::password name="password" :label="_i('Password')" />
        <x-larastrap::check name="remember" :label="_i('Ricordami')" checked="true" :attributes="['data-attribute' => 'remember_me', 'data-attribute-default' => 'true']" classes="remember-checkbox" value="1" />
    </x-larastrap::form>
</div>

<div class="col-12 col-md-6 offset-md-3 mb-5">
    <hr/>
    <p>
        @if($gas->hasFeature('public_registrations'))
            <a href="{{ route('register') }}">{{ _i('Registrati') }}</a>
        @endif
        <a class="float-end" href="{{ route('password.request') }}">{{ _i('Recupero Password') }}</a>
    </p>
    <br>
    <br>
    <br>
</div>

<nav class="fixed-bottom border-top px-0 pt-3 bg-light">
    <div class="container">
        <p>
            Powered by <a href="https://www.gasdotto.net/"><img src="{{ url('images/gasdotto.jpg') }}" style="height: 24px" alt="GASdotto"> GASdotto</a>
        </p>
    </div>
</nav>

@endsection
