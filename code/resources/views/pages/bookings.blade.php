@extends('app')

@section('content')

<div class="row">
    <div class="col-md-12">
        @include('commons.orderslist', ['orders' => $orders])
    </div>
</div>

@endsection
