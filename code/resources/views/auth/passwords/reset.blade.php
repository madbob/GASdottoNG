@extends('app')

@section('content')

<div class="col-12 col-md-6 offset-md-3">
    <x-larastrap::form method="POST" :action="route('password.update')" :buttons="[['color' => 'success', 'type' => 'submit', 'label' => _i('Aggiorna Password')]]">
        <input type="hidden" name="token" value="{{ $token }}">
        <x-larastrap::text name="email" :label="_i('E-Mail')" required />
        <x-larastrap::password name="password" :label="_i('Password')" required />
        <x-larastrap::password name="password_confirmation" :label="_i('Conferma Password')" required />
    </x-larastrap::form>
</div>

@endsection
