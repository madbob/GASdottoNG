<?php $size = 12 / (count($columns) + 1) ?>

<div class="many-rows">
	@if($contents->isEmpty())
		<div class="row">
			@foreach($columns as $column)
			<div class="col-md-{{ $size }}">
				@include('commons.' . $column['type'] . 'field', ['obj' => null, 'name' => $column['field'], 'label' => $column['label'], 'squeeze' => true])
			</div>
			@endforeach
		</div>
	@else
		@foreach($contents as $content)
			<div class="row">
				@foreach($columns as $column)
				<div class="col-md-{{ $size }}">
					@include('commons.' . $column['type'] . 'field', ['obj' => $content, 'name' => $column['field'], 'label' => $column['label'], 'squeeze' => true])
				</div>
				@endforeach
			</div>
		@endforeach
	@endif

	<button class="btn btn-default add-many-rows">Aggiungi Nuovo</button>
</div>
