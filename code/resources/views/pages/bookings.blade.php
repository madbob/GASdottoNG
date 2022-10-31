@extends('app')

@section('content')

<div class="row mb-5">
    <div class="col">
        @include('commons.orderslist', ['orders' => $orders])
    </div>
</div>

@endsection
