@extends($theme_layout)

@section('content')

<div class="row">
	<div class="col-md-12">
		@if($currentgas->userCan('movements.admin'))

		@include('commons.addingbutton', [
			'template' => 'movement.base-edit',
			'typename' => 'movement',
			'typename_readable' => 'Movimento',
			'targeturl' => 'movements'
		])

		@endif
	</div>

	<div class="clearfix"></div>
	<hr/>
</div>

<div class="row">
	<form class="form-horizontal form-filler" action="{{ url('movements') }}" data-toggle="validator" data-fill-target="#movements-in-range">
		<div class="col-md-6">
			<div class="form-group">
				<label for="start" class="col-sm-{{ $labelsize }} control-label">Dal</label>
				<div class="col-sm-{{ $fieldsize }}">
					<input type="text" class="date form-control" name="start" value="{{ ucwords(strftime('%A %d %B %G', strtotime('-1 months'))) }}" required autocomplete="off">
				</div>
			</div>

			<div class="form-group">
				<label for="end" class="col-sm-{{ $labelsize }} control-label">Al</label>
				<div class="col-sm-{{ $fieldsize }}">
					<input type="text" class="date form-control" name="end" value="{{ ucwords(strftime('%A %d %B %G', time())) }}" required autocomplete="off">
				</div>
			</div>

			<div class="form-group">
				<div class="col-sm-{{ $fieldsize }} col-md-offset-{{ $labelsize }}">
					<button type="submit" class="btn btn-success">Ricerca</button>
				</div>
			</div>
		</div>
	</form>
</div>

<hr/>

<div class="row">
	<div class="col-md-12" id="movements-in-range">
		@include('movement.list', ['movements' => $movements])
	</div>
</div>

@endsection
