<div class="form-group">
	@if($squeeze == false)
	<label for="{{ $prefix . $name . $postfix }}" class="col-sm-{{ $labelsize }} control-label">{{ $label }}</label>
	@endif

	<div class="col-sm-{{ $fieldsize }}">
		<textarea
			class="form-control"
			name="{{ $prefix . $name . $postfix }}"
			rows="5"

			@if($squeeze == true)
			placeholder="{{ $label }}"
			@endif

			autocomplete="off"><?php if($obj) echo $obj->$name ?></textarea>
	</div>
</div>
