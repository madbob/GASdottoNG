@extends('app')

@section('content')

@include('commons.statspage', ['target' => $currentgas])

@endsection
