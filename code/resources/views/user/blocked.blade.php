@extends('app')

@section('content')

<div class="col-12 col-md-6 offset-md-3">
    <div class="alert alert-info text-center">
        {{ _i('Il tuo utente non è ancora stato convalidato dagli amministratori. Quando sarà revisionato, riceverai una email di notifica.') }}
    </div>
</div>

@endsection
