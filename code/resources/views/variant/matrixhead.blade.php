<thead>
	<tr>
		@foreach($combos->first()->values as $value)
			<th scope="col">{{ $value->variant->name }}</th>
		@endforeach

		<th scope="col" width="15%">{{ __('texts.products.bookable') }}</th>

		<th scope="col" width="20%">
			{{ __('texts.products.code') }}
			<x-larastrap::pophelp ttext="products.variant.help.code" />
		</th>
		<th scope="col" width="20%">
			{{ __('texts.products.variant.price_difference') }}
			<x-larastrap::pophelp ttext="__('texts.products.variant.help.price_difference')" />
		</th>

		@if($product->measure->discrete)
			<th scope="col" width="20%">{{ __('texts.products.variant.weight_difference') }}</th>
		@endif
	</tr>
</thead>
