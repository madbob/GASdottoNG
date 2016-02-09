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
		<link rel="stylesheet" type="text/css" href="{{ url('css/bootstrap-table.css') }}">
		<link rel="stylesheet" type="text/css" href="{{ url('css/gasdotto.css') }}">

		<meta name="csrf-token" content="{{ csrf_token() }}"/>
	</head>
	<body>
		<nav class="navbar navbar-default navbar-fixed-top navbar-inverse">
			<div class="container-fluid">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<a class="navbar-brand" href="#">GASdotto</a>
				</div>

				<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
					@if(isset($menu))
					{!! $menu !!}
					@endif

					@if(Auth::check())
					<ul class="nav navbar-nav navbar-right">
						<li id="help-trigger"><a href="#">Aiuto <span class="glyphicon glyphicon-question-sign" aria-hidden="true"></span></a></li>
						<li><a href="{{ url('auth/logout') }}">Logout <span class="glyphicon glyphicon-off" aria-hidden="true"></span></a></li>
					</ul>
					@endif
				</div>
			</div>
		</nav>

		<div class="container-fluid">
			<div class="row">
				<div class="col-md-12" id="main-contents">
					@include('commons.flashing')
					@yield('content')
				</div>
			</div>
		</div>

		<div id="bottom-stop"></div>

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
		<script type="application/javascript" src="{{ url('js/bootstrap-table.js') }}"></script>
		<script type="application/javascript" src="{{ url('js/bootstrap-table-it-IT.js') }}"></script>
		<script type="application/javascript" src="{{ url('js/marked.js') }}"></script>
		<script type="application/javascript" src="{{ url('js/gasdotto.js') }}"></script>
	</body>
</html>
