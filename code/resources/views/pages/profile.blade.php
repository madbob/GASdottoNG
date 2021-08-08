@extends('app')

@section('content')

<div class="row">
    <div class="col">
        @include('user.edit', [
            'user' => $user,
            'active_tab' => $active_tab,
            'display_page' => true,
        ])
    </div>
</div>

@endsection
