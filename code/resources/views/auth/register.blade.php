@extends('app')

@section('content')

<div class="col-12 col-md-6 offset-md-3 mt-3">
    <x-larastrap::form method="POST" action="{{ route('register') }}" :buttons="[['type' => 'submit', 'color' => 'success', 'tlabel' => 'auth.register']]">
        <x-larastrap::text name="firstname" tlabel="user.firstname" :required="in_array('firstname', currentAbsoluteGas()->public_registrations['mandatory_fields'])" />
        <x-larastrap::text name="lastname" tlabel="user.lastname" :required="in_array('lastname', currentAbsoluteGas()->public_registrations['mandatory_fields'])" />
        <x-larastrap::email name="email" tlabel="generic.email" :required="in_array('email', currentAbsoluteGas()->public_registrations['mandatory_fields'])" />
        <x-larastrap::text name="phone" tlabel="generic.phone" :required="in_array('phone', currentAbsoluteGas()->public_registrations['mandatory_fields'])" />
        <x-larastrap::text name="username" tlabel="auth.username" required pattern="{{ usernamePattern() }}" />
        <x-larastrap::password name="password" tlabel="auth.password" required />
        <x-larastrap::password name="password_confirmation" tlabel="auth.confirm_password" required />
        <x-larastrap::text name="verify" :label="$captcha" required />

        @if(App\Gas::count() > 1)
            <x-larastrap::select-model name="gas_id" tlabel="generic.gas" required :options="App\Gas::orderBy('name', 'asc')->get()" />
        @else
            <input type="hidden" name="gas_id" value="{{ currentAbsoluteGas()->id }}">
        @endif

        @if (!empty(currentAbsoluteGas()->public_registrations['privacy_link']))
            <?php $privacy_claim = __('auth.accept_privacy', ['link' => currentAbsoluteGas()->public_registrations['privacy_link']]) ?>
            <x-larastrap::check name="privacy" :label="ue('<span>' . $privacy_claim . '</span>')" required />
        @endif

        @if (!empty(currentAbsoluteGas()->public_registrations['terms_link']))
            <?php $terms_claim = __('commons.accept_conditions', ['link' => currentAbsoluteGas()->public_registrations['terms_link']]) ?>
            <x-larastrap::check name="terms" :label="ue('<span>' . $terms_claim . '</span>')" required />
        @endif
    </x-larastrap::form>
</div>

<div class="col-12 col-md-6 offset-md-3">
    <hr/>
    <p>
        <a href="{{ route('login') }}">{{ __('auth.login') }}</a>
    </p>
</div>

@endsection
