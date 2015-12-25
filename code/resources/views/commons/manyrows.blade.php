<?php $column_size = floor(11 / count($columns)) ?>

<div class="many-rows">
	@if($contents->isEmpty())
		<div class="row">
			@foreach($columns as $column)
			<div class="col-md-{{ $column_size }}">
				<?php

				$attributes = [
					'obj' => null,
					'name' => $column['field'],
					'label' => $column['label'],
					'prefix' => $prefix,
					'postfix' => '[]',
					'squeeze' => true
				];

				if (isset($column['extra']))
					$attributes = array_merge($attributes, $column['extra']);

				?>

				@include('commons.' . $column['type'] . 'field', $attributes)
			</div>
			@endforeach
		</div>
	@else
		@foreach($contents as $content)
			<div class="row">
				@foreach($columns as $column)
				<div class="col-md-{{ $column_size }}">
					<?php

					$attributes = [
						'obj' => $content,
						'name' => $column['field'],
						'label' => $column['label'],
						'prefix' => $prefix,
						'postfix' => '[]',
						'squeeze' => true
					];

					if (isset($column['extra']))
						$attributes = array_merge($attributes, $column['extra']);

					?>

					@include('commons.' . $column['type'] . 'field', $attributes)
				</div>
				@endforeach
			</div>
		@endforeach
	@endif

	<button class="btn btn-default add-many-rows">Aggiungi Nuovo</button>
</div>
