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
				<small>{!! $price !!}</small>
			</div>
		</div>
	@endforeach
</div>
