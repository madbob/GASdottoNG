@extends('app')

@section('content')

<div class="col-12 col-md-6 offset-md-3 mb-5">
    @if($gas->message != '')
        <x-larastrap::suggestion>
            {!! nl2br($gas->message) !!}
        </x-larastrap::suggestion>
        <hr/>
    @endif

    @if($gas->restricted == '1')
        <div class="alert alert-warning text-center mt-3">
            {{ __('texts.auth.maintenance_notice') }}
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

    <x-larastrap::form method="POST" action="{{ route('login') }}" :buttons="[['color' => 'success', 'tlabel' => 'auth.login', 'type' => 'submit']]">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="language" value="{{ $gas->getConfig('language') }}">

        <x-larastrap::username name="username" tlabel="auth.username" />
        <x-larastrap::password name="password" tlabel="auth.password" />
        <x-larastrap::check name="remember" tlabel="auth.remember" checked="true" :attributes="['data-attribute' => 'remember_me', 'data-attribute-default' => 'true']" classes="remember-checkbox" value="1" />
    </x-larastrap::form>
</div>

<div class="col-12 col-md-6 offset-md-3 mb-5">
    <hr/>
    <p>
        @if($gas->hasFeature('public_registrations'))
            <a href="{{ route('register') }}">{{ __('texts.auth.register') }}</a>
        @endif
        <a class="float-end" href="{{ route('password.request') }}">{{ __('texts.auth.password_request_link') }}</a>
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
