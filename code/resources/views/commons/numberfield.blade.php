<div class="form-group">
	@if($squeeze == false)
	<label for="{{ $prefix . $name }}" class="col-sm-3 control-label">{{ $label }}</label>
	@endif

	<div class="col-sm-{{ $fieldsize }}">
		<input type="number"
			class="form-control"
			name="{{ $prefix . $name }}"
			value="<?php if($obj) echo $obj->$name ?>"

			@if(isset($mandatory) && $mandatory == true)
			required
			@endif

			@if($squeeze == true)
			placeholder="{{ $label }}"
			@endif

			autocomplete="off">
	</div>
</div>
