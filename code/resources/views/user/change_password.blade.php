@extends('app')

@section('content')

<div class="col-12 col-md-6 offset-md-3">
    <x-larastrap::suggestion>
        {{ __('auth.help.required_new_password') }}
    </x-larastrap::suggestion>

    <hr/>

    <x-larastrap::iform method="PUT" action="{{ route('users.update', $currentuser->id) }}" :buttons="[['type' => 'submit', 'color' => 'success', 'label' => __('generic.save_and_proceed')]]">
        <input type="hidden" name="reload-whole-page" value="1">
        @include('commons.passwordfield', ['obj' => null, 'name' => 'password', 'label' => __('auth.password'), 'mandatory' => true, 'enforcable_change' => false])
    </x-larastrap::iform>
</div>

@endsection
