<div class="form-group">
	<label class="col-sm-{{ $labelsize }} control-label">{{ $label }}</label>
	<div class="col-sm-{{ $fieldsize }}">
		<label class="static-label text-muted" data-updatable-name="movement-date-{{ $rand or rand() }}" data-updatable-field="registration_date">
			@if (!$obj || empty($obj->registration_date) || strstr($obj->registration_date, '0000-00-00') !== false)
				Mai
			@else
				{{ $obj->printableDate('registration_date') }} <span class="glyphicon {{ $obj->payment_icon }}" aria-hidden="true"></span>
			@endif
		</label>
	</div>
</div>
