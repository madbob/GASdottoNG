@extends('app')

@section('content')

@can('notifications.admin', $currentgas)
    <div class="row">
        <div class="col">
            @include('commons.addingbutton', [
                'template' => 'notification.create',
                'typename' => 'notification',
                'typename_readable' => __('texts.notifications.name'),
                'targeturl' => 'notifications'
            ])
        </div>
    </div>

    <hr/>
@endcan

<div class="row">
    <div class="col-12 col-md-6">
        <x-filler :data-action="route('notifications.search')" data-fill-target="#main-notifications-list">
            @include('commons.genericdaterange', [
                'start_date' => strtotime('-1 years'),
                'end_date' => strtotime('+1 years'),
            ])
        </x-filler>
    </div>
</div>

<hr/>

<div class="row">
    <div class="col" id="main-notifications-list">
        @include('commons.loadablelist', ['identifier' => 'notification-list', 'items' => $notifications])
    </div>
</div>

@endsection
