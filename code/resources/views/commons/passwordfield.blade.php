<div class="form-group">
	<label for="{{ $prefix . $name }}" class="col-sm-3 control-label">{{ $label }}</label>
	<div class="col-sm-{{ $fieldsize }}">
		<input
			type="password"
			class="form-control"

			@if($obj == null && isset($mandatory) && $mandatory == true)
			required
			@endif

			@if($obj != null)
			placeholder="Lascia vuoto per non modificare la password"
			@endif

			name="{{ $prefix . $name }}">
	</div>
</div>
