@extends('app')

@section('content')

<div class="col-md-6 col-md-offset-3">
    <form class="form-horizontal" method="POST" action="{{ route('register') }}">
        {{ csrf_field() }}

        <div class="form-group{{ $errors->has('firstname') ? ' has-error' : '' }}">
            <label for="firstname" class="col-md-4 control-label">{{ _i('Nome') }}</label>
            <div class="col-md-6">
                <input id="firstname" type="text" class="form-control" name="firstname" value="{{ old('firstname') }}" {{ in_array('firstname', currentAbsoluteGas()->public_registrations['mandatory_fields']) ? 'required' : '' }} autofocus>

                @if ($errors->has('firstname'))
                    <span class="help-block">
                        <strong>{{ $errors->first('firstname') }}</strong>
                    </span>
                @endif
            </div>
        </div>

        <div class="form-group{{ $errors->has('lastname') ? ' has-error' : '' }}">
            <label for="lastname" class="col-md-4 control-label">{{ _i('Cognome') }}</label>
            <div class="col-md-6">
                <input id="lastname" type="text" class="form-control" name="lastname" value="{{ old('lastname') }}" {{ in_array('lastname', currentAbsoluteGas()->public_registrations['mandatory_fields']) ? 'required' : '' }}>

                @if ($errors->has('lastname'))
                    <span class="help-block">
                        <strong>{{ $errors->first('lastname') }}</strong>
                    </span>
                @endif
            </div>
        </div>

        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
            <label for="email" class="col-md-4 control-label">{{ _i('E-Mail') }}</label>

            <div class="col-md-6">
                <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" {{ in_array('email', currentAbsoluteGas()->public_registrations['mandatory_fields']) ? 'required' : '' }}>

                @if ($errors->has('email'))
                    <span class="help-block">
                        <strong>{{ $errors->first('email') }}</strong>
                    </span>
                @endif
            </div>
        </div>

        <div class="form-group{{ $errors->has('phone') ? ' has-error' : '' }}">
            <label for="phone" class="col-md-4 control-label">{{ _i('Telefono') }}</label>

            <div class="col-md-6">
                <input id="phone" type="text" class="form-control" name="phone" value="{{ old('phone') }}" {{ in_array('phone', currentAbsoluteGas()->public_registrations['mandatory_fields']) ? 'required' : '' }}>

                @if ($errors->has('phone'))
                    <span class="help-block">
                        <strong>{{ $errors->first('phone') }}</strong>
                    </span>
                @endif
            </div>
        </div>

        <div class="form-group{{ $errors->has('username') ? ' has-error' : '' }}">
            <label for="username" class="col-md-4 control-label">{{ _i('Username') }}</label>
            <div class="col-md-6">
                <input id="username" type="text" class="form-control" name="username" value="{{ old('username') }}" required>

                @if ($errors->has('username'))
                    <span class="help-block">
                        <strong>{{ $errors->first('username') }}</strong>
                    </span>
                @endif
            </div>
        </div>

        <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
            <label for="password" class="col-md-4 control-label">{{ _i('Password') }}</label>

            <div class="col-md-6">
                <input id="password" type="password" class="form-control" name="password" required>

                @if ($errors->has('password'))
                    <span class="help-block">
                        <strong>{{ $errors->first('password') }}</strong>
                    </span>
                @endif
            </div>
        </div>

        <div class="form-group">
            <label for="password-confirm" class="col-md-4 control-label">{{ _i('Conferma Password') }}</label>

            <div class="col-md-6">
                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required>
            </div>
        </div>

        @if(App\Gas::count() > 1)
            <div class="form-group">
                <label for="gas_id" class="col-md-4 control-label">{{ _i('GAS') }}</label>

                <div class="col-md-6">
                    <select id="gas_id" class="form-control" name="gas_id">
                        @foreach(App\Gas::orderBy('name', 'asc')->get() as $g)
                            <option value="{{ $g->id }}">{{ $g->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        @else
            <input type="hidden" name="gas_id" value="{{ currentAbsoluteGas()->id }}">
        @endif

        @if (!empty(currentAbsoluteGas()->public_registrations['privacy_link']) || env('GASDOTTO_NET', false) == true)
            <div class="form-group">
                <div class="col-sm-offset-4 col-sm-6">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" required> {!! _i("Ho letto e accetto l'<a href=\"%s\" target=\"_blank\">Informativa sulla Privacy</a>.", [empty(currentAbsoluteGas()->public_registrations['privacy_link']) ? 'http://gasdotto.net/privacy' : currentAbsoluteGas()->public_registrations['privacy_link']]) !!}
                        </label>
                    </div>
                </div>
            </div>
        @endif

        <br>

        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <button class="btn btn-success pull-right" type="submit">{{ _i('Registrati') }}</button>
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
