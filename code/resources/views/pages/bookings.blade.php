@extends('app')

@section('content')

<div class="row">
    <div class="col">
        @include('commons.orderslist', ['orders' => $orders])
    </div>
</div>

@endsection
