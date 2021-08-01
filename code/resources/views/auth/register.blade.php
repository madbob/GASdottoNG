@extends('app')

@section('content')

<div class="col-12 col-md-6 offset-md-3 mt-3">
    <x-larastrap::form method="POST" action="{{ route('register') }}" :buttons="[['type' => 'submit', 'color' => 'success', 'label' => _i('Registrati')]]">
        <x-larastrap::text name="firstname" :label="_i('Nome')" :required="in_array('firstname', currentAbsoluteGas()->public_registrations['mandatory_fields'])" />
        <x-larastrap::text name="lastname" :label="_i('Cognome')" :required="in_array('lastname', currentAbsoluteGas()->public_registrations['mandatory_fields'])" />
        <x-larastrap::email name="email" :label="_i('E-Mail')" :required="in_array('email', currentAbsoluteGas()->public_registrations['mandatory_fields'])" />
        <x-larastrap::text name="phone" :label="_i('Telefono')" :required="in_array('phone', currentAbsoluteGas()->public_registrations['mandatory_fields'])" />
        <x-larastrap::text name="username" :label="_i('Username')" required pattern="{{ App\User::usernamePattern() }}" />
        <x-larastrap::password name="password" :label="_i('Password')" required />
        <x-larastrap::password name="password_confirmation" :label="_i('Conferma Password')" required />
        <x-larastrap::text name="verify" :label="$captcha" required />

        @if(App\Gas::count() > 1)
            <x-larastrap::selectobj name="gas_id" :label="_('GAS')" required :options="App\Gas::orderBy('name', 'asc')->get()" />
        @else
            <input type="hidden" name="gas_id" value="{{ currentAbsoluteGas()->id }}">
        @endif

        @if (!empty(currentAbsoluteGas()->public_registrations['privacy_link']))
            <?php $privacy_claim = _i("Ho letto e accetto l'<a href=\"%s\" target=\"_blank\">Informativa sulla Privacy</a>.", [currentAbsoluteGas()->public_registrations['privacy_link']]) ?>
            <x-larastrap::scheck name="privacy" :label="$privacy_claim" required />
        @endif

        @if (!empty(currentAbsoluteGas()->public_registrations['terms_link']))
            <?php $terms_claim = _i("Ho letto e accetto le <a href=\"%s\" target=\"_blank\">Condizioni d'Uso</a>.", [currentAbsoluteGas()->public_registrations['terms_link']]) ?>
            <x-larastrap::scheck name="terms" :label="$terms_claim" required />
        @endif
    </x-larastrap::form>
</div>

<div class="col-12 col-md-6 offset-md-3">
    <hr/>
    <p>
        <a href="{{ route('login') }}">{{ _i('Login') }}</a>
    </p>
</div>

@endsection
