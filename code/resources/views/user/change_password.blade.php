@extends('app')

@section('content')

<div class="col-12 col-md-6 offset-md-3">
    <div class="alert alert-info">
        {{ _i('Per procedere devi settare una nuova password per il tuo profilo.') }}
    </div>

    <hr/>

    <x-larastrap::iform method="PUT" action="{{ route('users.update', $currentuser->id) }}" :buttons="[['type' => 'submit', 'color' => 'success', 'label' => _i('Salva e Procedi')]]">
        <input type="hidden" name="reload-whole-page" value="1">
        @include('commons.passwordfield', ['obj' => null, 'name' => 'password', 'label' => _i('Password'), 'mandatory' => true, 'enforcable_change' => false])
    </x-larastrap::iform>
</div>

@endsection
