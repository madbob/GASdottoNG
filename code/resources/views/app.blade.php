<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">

		<title>GASdotto</title>

		<link rel="stylesheet" type="text/css" href="{{ url('css/bootstrap.min.css') }}">
		<link rel="stylesheet" type="text/css" href="{{ url('css/bootstrap-datepicker3.min.css') }}">
		<link rel="stylesheet" type="text/css" href="{{ url('css/bootstrap-multiselect.css') }}">
		<link rel="stylesheet" type="text/css" href="{{ url('css/bootstrap-tagsinput.css') }}">
		<link rel="stylesheet" type="text/css" href="{{ url('css/jstree.css') }}">
		<link rel="stylesheet" type="text/css" href="{{ url('css/gasdotto.css') }}">

		<meta name="csrf-token" content="{{ csrf_token() }}"/>
	</head>
	<body>
		@if(Auth::check())
		<nav class="navbar navbar-default topbar">
			<div class="container-fluid">
				<div class="collapse navbar-collapse">
					<ul class="nav navbar-nav">
						<li><a href="{{ url('/') }}">{{ $currentuser->printableName() }} @ {{ $currentgas->name }}</a></li>
					</ul>
					<ul class="nav navbar-nav navbar-right">
						<li><a href="{{ url('auth/logout') }}">Logout <span class="glyphicon glyphicon-off" aria-hidden="true"></span></a></li>
					</ul>
				</div>
			</div>
		</nav>
		@endif

		<div class="container-fluid">
			<div class="row">
				<div class="col-md-2">
					@if(isset($menu))
					<nav class="navbar navbar-default sidebar" role="navigation">
						<div class="navbar-header">
							<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
								<span class="icon-bar"></span>
								<span class="icon-bar"></span>
								<span class="icon-bar"></span>
							</button>
						</div>

						<div class="collapse navbar-collapse">
							{!! $menu !!}
						</div>
					</nav>
					@endif
				</div>

				<div class="col-md-10" id="main-contents">
					@include('commons.flashing')
					@yield('content')
				</div>
			</div>
		</div>

		<script type="application/javascript" src="{{ url('js/jquery-2.1.1.min.js') }}"></script>
		<script type="application/javascript" src="{{ url('js/jquery-ui.js') }}"></script>
		<script type="application/javascript" src="{{ url('js/bootstrap.min.js') }}"></script>
		<script type="application/javascript" src="{{ url('js/bootstrap-datepicker.min.js') }}"></script>
		<script type="application/javascript" src="{{ url('js/bootstrap-datepicker.it.min.js') }}"></script>
		<script type="application/javascript" src="{{ url('js/bootstrap-multiselect.js') }}"></script>
		<script type="application/javascript" src="{{ url('js/bootstrap-tagsinput.js') }}"></script>
		<script type="application/javascript" src="{{ url('js/jstree.js') }}"></script>
		<script type="application/javascript" src="{{ url('js/typeahead.bundle.js') }}"></script>
		<script type="application/javascript" src="{{ url('js/validator.min.js') }}"></script>
		<script type="application/javascript" src="{{ url('js/jquery.fileupload.js') }}"></script>
		<script type="application/javascript" src="{{ url('js/gasdotto.js') }}"></script>
	</body>
</html>
