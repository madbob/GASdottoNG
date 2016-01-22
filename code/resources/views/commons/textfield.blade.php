<div class="form-group">
	@if($squeeze == false)
	<label for="{{ $prefix . $name . $postfix }}" class="col-sm-{{ $labelsize }} control-label">{{ $label }}</label>
	@endif

	<div class="col-sm-{{ $fieldsize }}">
		<input type="text"
			class="form-control"
			name="{{ $prefix . $name . $postfix }}"
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
