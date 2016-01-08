@extends($theme_layout)

@section('content')

<div class="col-md-7">
	<form class="form-horizontal" method="POST" action="{{ url('/auth/login') }}">
		<input type="hidden" name="_token" value="{{ csrf_token() }}">

		<div class="form-group">
			<label class="col-sm-2 control-label">Username</label>
			<div class="col-sm-10">
				<input class="form-control" type="text" name="username" value="{{ old('username') }}">
			</div>
		</div>

		<div class="form-group">
			<label class="col-sm-2 control-label">Password</label>
			<div class="col-sm-10">
				<input class="form-control" type="password" name="password">
			</div>
		</div>

		<div class="form-group">
			<div class="col-sm-offset-2 col-sm-10">
				<div class="checkbox">
					<label>
						<input type="checkbox" name="remember"> Ricordami
					</label>
				</div>
			</div>
		</div>

		<br>

		<div class="form-group">
			<div class="col-sm-offset-2 col-sm-10">
				<button class="btn btn-success pull-right" type="submit">Login</button>
			</div>
		</div>
	</form>
</div>

@endsection
