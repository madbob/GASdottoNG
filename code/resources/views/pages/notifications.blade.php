@extends('app')

@section('content')

@can('notifications.admin', $currentgas)
    <div class="row">
        <div class="col-md-12">
            @include('commons.addingbutton', [
                'template' => 'notification.create',
                'typename' => 'notification',
                'typename_readable' => _i('Notifica'),
                'targeturl' => 'notifications'
            ])
        </div>

        <div class="clearfix"></div>
        <hr/>
    </div>
@endcan

<div class="row">
    <div class="col-md-6">
        <div class="form-horizontal form-filler" data-action="{{ route('notifications.search') }}" data-toggle="validator" data-fill-target="#main-notifications-list">
            @include('commons.genericdaterange', [
                'start_date' => strtotime('-1 years'),
                'end_date' => strtotime('+1 years'),
            ])
            <div class="form-group">
                <div class="col-md-{{ $fieldsize }} col-md-offset-{{ $labelsize }}">
                    <button type="submit" class="btn btn-info">{{ _i('Ricerca') }}</button>
                </div>
            </div>
        </div>
    </div>

    <div class="clearfix"></div>
    <hr/>
</div>

<div class="row">
    <div class="col-md-12" id="main-notifications-list">
        @include('commons.loadablelist', ['identifier' => 'notification-list', 'items' => $notifications])
    </div>
</div>

@endsection
