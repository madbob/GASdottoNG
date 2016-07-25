<?php

if (isset($fixed_value) && $fixed_value != false) {
	$value = $fixed_value;
	$disabled = true;
}
else {
	if ($obj)
		$value = $obj->$name;
	else
		$value = '';

	$disabled = isset($disabled) ? $disabled : false;
}

?>

<div class="form-group">
	@if($squeeze == false)
	<label for="{{ $prefix . $name . $postfix }}" class="col-sm-{{ $labelsize }} control-label">{{ $label }}</label>
	@endif

	<div class="col-sm-{{ $fieldsize }}">
		@if(isset($postlabel))
		<div class="input-group">
		@endif

		<input type="number"
			class="form-control"
			name="{{ $prefix . $name . $postfix }}"
			step="0.01"
			min="0"
			value="{{ $value }}"

			@if(isset($mandatory) && $mandatory == true)
			required
			@endif

			@if(isset($disabled) && $disabled == true)
			disabled
			@endif

			@if($squeeze == true)
			placeholder="{{ $label }}"
			@endif

			autocomplete="off">

		@if(isset($postlabel))
		<div class="input-group-addon">{{ $postlabel }}</div>
		</div>
		@endif
	</div>
</div>
