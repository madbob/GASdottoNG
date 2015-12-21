<div class="form-group">
	<label class="col-sm-3 control-label">{{ $label }}</label>
	<div class="col-sm-9">
		<select class="form-control" name="{{ $name }}">
			@foreach($objects as $o)
			<option value="{{ $o->id }}"<?php if($obj && $obj->$name == $o->id) echo ' selected="selected"' ?>>{{ $o->printableName() }}</option>
			@endforeach
		</select>
	</div>
</div>
