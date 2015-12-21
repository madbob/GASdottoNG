<div class="form-group">
	<label for="{{ $name }}" class="col-sm-3 control-label">{{ $label }}</label>
	<div class="col-sm-9">
		<input type="checkbox"
			name="{{ $name }}"
			class="checkbox"
			<?php if($obj && $obj->$name == true) echo ' checked="checked"' ?>
			autocomplete="off">
	</div>
</div>
