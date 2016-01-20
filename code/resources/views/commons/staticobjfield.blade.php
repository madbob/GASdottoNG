<div class="form-group">
	<label class="col-sm-3 control-label">{{ $label }}</label>
	<div class="col-sm-{{ $fieldsize }}">
		<label class="static-label text-muted">
			@if($obj != null && $obj->$name != null)
			{{ $obj->$name->printableName() }}
			@else
			Nessuno
			@endif
		</label>
	</div>
</div>
