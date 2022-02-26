<?php

$prices = [];

if ($product->variants->isEmpty() == false) {
	if ($booked != null && $booked->variants->count() != 0) {
		foreach($booked->variants as $var) {
			$combo = $var->variantsCombo();
			$prices[] = $product->printablePrice($combo);
		}
	}
	else {
		$prices[] = $product->printablePrice(null);
	}
}
else {
	$prices[] = $product->printablePrice();
}

?>

<div class="prices_block">
	@foreach($prices as $price)
		<div class="row">
			<div class="col">
				<label class="static-label form-control-plaintext">
					<small>{!! $price !!}</small>
				</label>
			</div>
		</div>
	@endforeach
</div>

@if($product->variable)
	<small>
		<span class="d-none d-sm-block">{{ _i('(prodotto a prezzo variabile)') }}</span><span class="d-block d-sm-none">{{ _i('(variabile)') }}</span>
	</small>
@endif
