@extends('app')

@section('content')

<div class="col-12 col-md-6 offset-md-3">
    <x-larastrap::suggestion>
        {{ __('texts.auth.help.unconfirmed') }}
    </x-larastrap::suggestion>
</div>

@endsection
