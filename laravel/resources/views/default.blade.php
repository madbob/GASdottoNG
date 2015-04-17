@extends('app')

@section('content')
<div class="container-fluid">
	<div class="row">
		<div class="col-md-3 sidebar">
			<div class="panel panel-default">
				<ul class="list-group">
					<li class="list-group-item"><a href="/view/home">Home</a></li>
					<li class="list-group-item"><a href="/view/suppliers">Fornitori</a></li>
				</ul>
			</div>
		</div>
		<div class="col-md-9" id="area">
		</div>
	</div>
</div>
@endsection
