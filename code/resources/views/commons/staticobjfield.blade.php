<div class="form-group">
	<label class="col-sm-3 control-label">{{ $label }}</label>
	<div class="col-sm-9">
		<label class="control-label">
			@if($obj != null && $obj->$name() != null)
			{{ $obj->$name()->printableName() }}
			@else
			Nessuno
			@endif
		</label>
	</div>
</div>
