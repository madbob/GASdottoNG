<div class="form-group">
	@if($squeeze == false)
	<label for="{{ $prefix . $name . $postfix }}" class="col-sm-3 control-label">{{ $label }}</label>
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
			value="<?php if($obj) echo $obj->$name ?>"

			@if(isset($mandatory) && $mandatory == true)
			required
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
