<div class="form-group">
	<label for="{{ $prefix . $name }}" class="col-sm-{{ $labelsize }} control-label">{{ $label }}</label>
	<div class="col-sm-{{ $fieldsize }}">
		<input type="checkbox"
			name="{{ $prefix . $name }}"
			class="checkbox"
			<?php if($obj && $obj->$name == true) echo ' checked="checked"' ?>
			autocomplete="off">
	</div>
</div>
