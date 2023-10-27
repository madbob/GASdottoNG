@extends('app')

@section('content')

@php

if (isset($social) == false) {
    $social = null;
}

if (isset($email) == false) {
    $email = '';
}

if (isset($captcha) == false) {
    $captcha = null;
}

$gas = currentAbsoluteGas();
$mandatory_fields = $gas->public_registrations['mandatory_fields'];

@endphp

<div class="col-12 col-md-6 offset-md-3 mt-3 mb-5">
    <x-larastrap::form method="POST" action="{{ route('register') }}" :buttons="[['type' => 'submit', 'color' => 'success', 'label' => _i('Registrati')]]">
        @if($social)
            <x-larastrap::hidden name="social" :value="$social" />

            <div class="alert alert-info mb-3">
                {{ _i('Completa la tua registrazione con qualche informazione in più.') }}
            </div>
        @endif

        <x-larastrap::text name="firstname" :label="_i('Nome')" :required="in_array('firstname', $mandatory_fields)" />
        <x-larastrap::text name="lastname" :label="_i('Cognome')" :required="in_array('lastname', $mandatory_fields)" />
        <x-larastrap::email name="email" :label="_i('E-Mail')" :required="in_array('email', $mandatory_fields)" :value="$email" />
        <x-larastrap::text name="phone" :label="_i('Telefono')" :required="in_array('phone', $mandatory_fields)" />

        @if(is_null($social))
            <x-larastrap::text name="username" :label="_i('Username')" required pattern="{{ usernamePattern() }}" />
            <x-larastrap::password name="password" :label="_i('Password')" required />
            <x-larastrap::password name="password_confirmation" :label="_i('Conferma Password')" required />
        @endif

        @if($captcha)
            <x-larastrap::text name="verify" :label="$captcha" required />
        @endif

        @if(App\Gas::count() > 1)
            <x-larastrap::selectobj name="gas_id" :label="_('GAS')" required :options="App\Gas::orderBy('name', 'asc')->get()" />
        @else
            <input type="hidden" name="gas_id" value="{{ $gas->id }}">
        @endif

        @if (!empty($gas->public_registrations['privacy_link']))
            <?php $privacy_claim = _i("Ho letto e accetto l'<a href=\"%s\" target=\"_blank\">Informativa sulla Privacy</a>.", [$gas->public_registrations['privacy_link']]) ?>
            <x-larastrap::check name="privacy" :label="ue('<span>' . $privacy_claim . '</span>')" required />
        @endif

        @if (!empty($gas->public_registrations['terms_link']))
            <?php $terms_claim = _i("Ho letto e accetto le <a href=\"%s\" target=\"_blank\">Condizioni d'Uso</a>.", [$gas->public_registrations['terms_link']]) ?>
            <x-larastrap::check name="terms" :label="ue('<span>' . $terms_claim . '</span>')" required />
        @endif
    </x-larastrap::form>

    @if(is_null($social))
        @include('auth.socialbuttons')
    @endif
</div>

<div class="col-12 col-md-6 offset-md-3">
    <hr/>
    <p>
        <a href="{{ route('login') }}">{{ _i('Login') }}</a>
    </p>
</div>

@endsection
