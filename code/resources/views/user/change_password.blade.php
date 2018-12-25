@extends('app')

@section('content')

<div class="col-md-6 col-md-offset-3">
    <div class="alert alert-info">
        {{ _i('Per procedere devi settare una nuova password per il tuo profilo.') }}
    </div>

    <hr/>

    <form class="form-horizontal inner-form" method="PUT" action="{{ route('users.update', $currentuser->id) }}">
        <input type="hidden" name="reload-whole-page" value="1">
        @include('commons.passwordfield', ['obj' => null, 'name' => 'password', 'label' => _i('Password'), 'mandatory' => true, 'enforcable_change' => false])

        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <button class="btn btn-success pull-right" type="submit">{{ _i('Salva e Procedi') }}</button>
            </div>
        </div>
    </form>
</div>

@endsection
