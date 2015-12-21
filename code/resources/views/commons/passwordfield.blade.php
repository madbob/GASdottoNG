<div class="form-group">
	<label for="{{ $name }}" class="col-sm-3 control-label">{{ $label }}</label>
	<div class="col-sm-9">
		<input
			type="password"
			class="form-control"

			@if($obj == null && isset($mandatory) && $mandatory == true)
			required
			@endif

			@if($obj != null)
			placeholder="Lascia vuoto per non modificare la password"
			@endif

			name="{{ $name }}">
	</div>
</div>
