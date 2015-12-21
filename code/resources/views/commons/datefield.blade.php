<div class="form-group">
	<label for="{{ $name }}" class="col-sm-3 control-label">{{ $label }}</label>
	<div class="col-sm-9">
		<input type="text"
			class="date form-control"
			name="{{ $name }}"

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

			autocomplete="off">
	</div>
</div>
