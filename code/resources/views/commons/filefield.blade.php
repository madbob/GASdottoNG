<div class="form-group">
	@if($squeeze == false)
	<label for="{{ $prefix . $name . $postfix }}" class="col-sm-3 control-label">{{ $label }}</label>
	@endif

	<div class="col-sm-{{ $fieldsize }}">
		<input type="file"
			name="{{ $prefix . $name . $postfix }}"

			@if(isset($mandatory) && $mandatory == true)
			required
			@endif

			@if(!empty($extras))
				@foreach ($extras as $extra_key => $extra_value)
				{{ $extra_key }}='{{ $extra_value }}'
				@endforeach
			@endif

			autocomplete="off">
	</div>
</div>
