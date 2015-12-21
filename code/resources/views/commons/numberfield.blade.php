<?php

if (!isset($squeeze))
	$squeeze = false;

if ($squeeze == true)
	$size = 12;
else
	$size = 9;

?>

<div class="form-group">
	@if($squeeze == false)
	<label for="{{ $name }}" class="col-sm-3 control-label">{{ $label }}</label>
	@endif

	<div class="col-sm-{{ $size }}">
		<input type="number"
			class="form-control"
			name="{{ $name }}"
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
