<?php

if (isset($valuefrom) == false)
	$valuefrom = null;

?>

<div class="form-group">
	@if($squeeze == false)
	<label for="{{ $prefix . $name . $postfix }}" class="col-sm-{{ $labelsize }} control-label">{{ $label }}</label>
	@endif
	<div class="col-sm-{{ $fieldsize }}">
		<input type="checkbox"
			name="{{ $prefix . $name . $postfix }}"
			class="checkbox"
			<?php if($obj && $obj->$name == true) echo ' checked="checked"' ?>
			<?php if($obj && $valuefrom) echo ' value="' . $obj->$valuefrom . '"' ?>
			autocomplete="off">
	</div>
</div>
