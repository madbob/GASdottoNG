@extends('app')

@section('content')

<div class="col-12 col-md-6 offset-md-3 mt-3">
    <x-larastrap::form method="POST" :action="route('password.email')" :buttons="[['color' => 'success', 'type' => 'submit', 'tlabel' => 'auth.password_request_link']]">
        <x-larastrap::text name="username" tlabel="auth.reset_username" />
    </x-larastrap::form>
</div>

<div class="col-12 col-md-6 offset-md-3">
    <hr/>
    <p>
        <a href="{{ route('login') }}">{{ __('texts.auth.login') }}</a>
    </p>
</div>

@endsection
