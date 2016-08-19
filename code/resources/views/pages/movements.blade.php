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
			@include('commons.genericdaterange')

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
