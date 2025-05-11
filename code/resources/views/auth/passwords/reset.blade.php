@extends('app')

@section('content')

<div class="col-12 col-md-6 offset-md-3 mt-3">
    <x-larastrap::form method="POST" :action="route('password.update')" :buttons="[['color' => 'success', 'type' => 'submit', 'tlabel' => 'auth.update_password']]">
        <input type="hidden" name="token" value="{{ $token }}">
        <x-larastrap::text name="email" tlabel="generic.email" required />
        <x-larastrap::password name="password" tlabel="auth.password" required />
        <x-larastrap::password name="password_confirmation" tlabel="auth.confirm_password" required />
    </x-larastrap::form>
</div>

@endsection
