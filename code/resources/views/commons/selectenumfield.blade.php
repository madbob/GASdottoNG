<?php

$select_class = 'form-control';
if ($extra_class)
	$select_class .= ' ' . $extra_class;

?>

<div class="form-group">
	<label for="{{ $prefix . $name }}" class="col-sm-{{ $labelsize }} control-label">{{ $label }}</label>
	<div class="col-sm-{{ $fieldsize }}">
		<select class="{{ $select_class }}" name="{{ $prefix . $name }}" autocomplete="off">
			@foreach($values as $v)
			<option value="{{ $v['value'] }}"<?php if($obj && $obj->$name == $v['value']) echo ' selected="selected"' ?>>{{ $v['label'] }}</option>
			@endforeach
		</select>
	</div>
</div>
