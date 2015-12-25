<div class="form-group">
	<label for="{{ $prefix . $name }}" class="col-sm-3 control-label">{{ $label }}</label>
	<div class="col-sm-{{ $fieldsize }}">
		<select class="form-control" name="{{ $prefix . $name }}">
			@foreach($objects as $o)
			<option value="{{ $o->id }}"<?php if($obj && $obj->$name == $o->id) echo ' selected="selected"' ?>>{{ $o->printableName() }}</option>
			@endforeach
		</select>
	</div>
</div>
