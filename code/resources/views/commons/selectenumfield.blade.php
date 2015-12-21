<div class="form-group">
	<label class="col-sm-3 control-label">{{ $label }}</label>
	<div class="col-sm-9">
		<select class="form-control" name="{{ $name }}">
			@foreach($values as $v)
			<option value="{{ $v['value'] }}"<?php if($obj && $obj->$name == $v['value']) echo ' selected="selected"' ?>>{{ $v['label'] }}</option>
			@endforeach
		</select>
	</div>
</div>
