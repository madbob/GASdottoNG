@extends('app')

@section('content')

<div class="card mb-2">
    <div class="card-header">
        <h3>{{ __('generic.menu.configs') }}</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <x-larastrap::accordion always_open="true">
                    @include('gas.general')
                    @include('gas.users')
                    @include('gas.products')
                    @include('gas.orders')
                    @include('gas.accounting')
                    @include('gas.emails')
                    @include('gas.exports')
                </x-larastrap::accordion>
            </div>

            <div class="col-md-6">
                @include('gas.extras')
            </div>
        </div>
    </div>
</div>

@can('gas.permissions', $gas)
    <div id="permissions-management" class="card gas-permission-editor" data-fetch-url="{{ route('roles.index') }}">
        @include('permissions.gas-management', ['gas' => $gas])
    </div>
@endcan

<br>

@stack('postponed')

@endsection
