@extends('app')

@section('content')

<div class="row">
    <div class="col-md-12">
        @include('user.edit', [
            'user' => $user,
            'active_tab' => $active_tab,
            'booked_orders' => $booked_orders,
            'display_page' => true,
        ])
    </div>
</div>

@endsection
