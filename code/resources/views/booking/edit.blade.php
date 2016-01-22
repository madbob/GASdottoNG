<?php

$more_orders = ($aggregate->orders->count() > 1);

?>

<form class="form-horizontal inner-form booking-form" method="PUT" action="{{ url('booking/' . $aggregate->id . '/user/' . $user->id) }}">
	@foreach($aggregate->orders as $order)
		@if($more_orders)
			<h3>{{ $order->printableName() }}</h3>
		@endif

		<?php $o = $order->userBooking($user->id) ?>

		<table class="table table-striped booking-editor">
			<thead>
				<th width="25%"></th>
				<th width="25%"></th>
				<th width="35%"></th>
				<th width="15%"></th>
			</thead>
			<tbody>
				@foreach($order->products as $product)
				<tr class="booking-product">
					<td>
						<input type="hidden" name="product-minimum" value="{{ $product->minimum }}" class="skip-on-submit" />
						<input type="hidden" name="product-multiple" value="{{ $product->multiple }}" class="skip-on-submit" />
						<input type="hidden" name="product-partitioning" value="{{ $product->partitioning }}" class="skip-on-submit" />
						<input type="hidden" name="product-price" value="{{ $product->price + $product->transport }}" class="skip-on-submit" />

						<label class="static-label">{{ $product->name }}</label>
					</td>

					<td>
						<div class="input-group booking-product-quantity">
							<input type="number" class="form-control" name="{{ $product->id }}" value="{{ $o->getBookedQuantity($product) }}" />
							<div class="input-group-addon">{{ $product->printableMeasure() }}</div>
						</div>

						@if($product->variants->isEmpty() == false)
						<div class="variant-selector">
							@include('booking.variantselectrow', ['product' => $product, 'master' => true, 'saved' => null])

							<?php $booked = $o->getBooked($product) ?>
							@if($booked != null)
								@foreach($booked->variants as $var)
									@include('booking.variantselectrow', ['product' => $product, 'master' => false, 'saved' => $var])
								@endforeach
							@endif
						</div>
						@endif
					</td>

					<td>
						<label class="static-label">{{ $product->printableDetails() }}</label>
					</td>

					<td class="text-right">
						<label class="static-label">{!! $product->printablePrice() !!}</label>
					</td>
				</tr>
				@endforeach
			</tbody>
			<tfoot>
				<tr>
					<th></th>
					<th></th>
					<th></th>
					<th class="text-right">Totale: <span class="booking-total">{{ $o->value }}</span> €</th>
				</tr>
			</tfoot>
		</table>
	@endforeach

	<div class="row">
		<div class="col-md-12">
			<div class="btn-group pull-right main-form-buttons" role="group" aria-label="Opzioni">
				<button type="submit" class="btn btn-success">Salva</button>
			</div>
		</div>
	</div>
</form>
