<div class="form-group">
	<label for="{{ $prefix . $name }}" class="col-sm-3 control-label">{{ $label }}</label>
	<div class="col-sm-{{ $fieldsize }}">
		<select class="form-control<?php if($triggering_modal !== false) echo ' triggers-modal" data-trigger-modal="' . $triggering_modal ?>" name="{{ $prefix . $name }}">
			@foreach($objects as $o)
			<option value="{{ $o->id }}"<?php if($obj && $obj->$name == $o->id) echo ' selected="selected"' ?>>{{ $o->printableName() }}</option>
			@endforeach

			@if($triggering_modal !== false)
			<option value="run_modal">Crea Nuovo</option>
			@endif
		</select>
	</div>
</div>
