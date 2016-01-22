<div class="form-group">
	@if($squeeze == false)
	<label for="{{ $prefix . $name }}" class="col-sm-{{ $labelsize }} control-label">{{ $label }}</label>
	@endif

	<div class="col-sm-{{ $fieldsize }}">
		<input type="text"
			class="date form-control"
			name="{{ $prefix . $name }}"

			value="<?php
			if ($obj) {
				if ($obj->$name == '0000-00-00')
					echo '';
				else
					echo $obj->printableDate($name);
			}
			?>"

			@if(isset($mandatory) && $mandatory == true)
			required
			@endif

			@if($squeeze == true)
			placeholder="{{ $label }}"
			@endif

			autocomplete="off">
	</div>
</div>
