@extends('app')

@section('content')

<div class="col-md-6 col-md-offset-3">
    <form class="form-horizontal" role="form" method="POST" action="{{ route('password.email') }}">
        {{ csrf_field() }}

        <div class="form-group">
            <label class="col-sm-2 control-label">Username</label>
            <div class="col-sm-10">
                <input class="form-control" type="text" name="username" value="{{ old('username') }}">
            </div>
        </div>

        <br>

        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <button class="btn btn-success pull-right" type="submit">Chiedi Reset Password</button>
            </div>
        </div>
    </form>
</div>

<div class="col-md-6 col-md-offset-3">
    <hr/>
    <p>
        <a href="{{ route('login') }}">{{ _i('Login') }}</a>
    </p>
</div>

@endsection
