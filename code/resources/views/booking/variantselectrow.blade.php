<div class="row<?php if($master == true) echo ' master-variant-selector' ?>">
	<div class="input-group booking-product-quantity col-md-{{ 12 - ($product->variants->count() * 3) - 2 }}">
		<input type="number" step="any" min="0" class="form-control<?php if($master == true) echo ' skip-on-submit' ?>" name="variant_quantity_{{ $product->id }}[]" value="{{ ($saved != null) ? $saved->quantity : '0' }}" />
		<div class="input-group-addon">{{ $product->printableMeasure() }}</div>
	</div>

	@foreach($product->variants as $variant)
	<div class="col-md-3">
		<select class="form-control<?php if($master == true) echo ' skip-on-submit' ?>" name="variant_selection_{{ $variant->id }}[]">
			@foreach($variant->values as $value)
			<option value="{{ $value->id }}"<?php if($saved != null && $saved->hasCombination($variant, $value)) echo ' selected="selected"' ?>>{{ $value->printableFullValue() }}</option>
			@endforeach
		</select>
	</div>
	@endforeach

	<div class="col-md-2">
		<button class="btn btn-default pull-right add-variant"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span></button>
	</div>
</div>
