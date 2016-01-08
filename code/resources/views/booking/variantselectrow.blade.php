<div class="row<?php if($master == true) echo ' master-variant-selector' ?>">
	<?php $size = ceil(12 / $product->variants()->count()) ?>
	@foreach($product->variants as $variant)
	<div class="col-md-{{ $size }}">
		<select class="form-control<?php if($master == true) echo ' skip-on-submit' ?>" name="{{ $variant->id }}[]">
			@foreach($variant->values as $value)
			<option value="{{ $value->id }}"<?php if($saved != null && $saved->hasCombination($variant, $value)) echo ' selected="selected"' ?>>{{ $value->printableFullValue() }}</option>
			@endforeach
		</select>
	</div>
	@endforeach
</div>
