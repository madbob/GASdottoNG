<?php

$more_orders = ($aggregate->orders->count() > 1);

?>

<form class="form-horizontal inner-form booking-form" method="PUT" action="{{ url('delivery/' . $aggregate->id . '/user/' . $user->id) }}">
	@foreach($aggregate->orders as $order)
		@if($more_orders)
			<h3>{{ $order->printableName() }}</h3>
		@endif

		<?php $o = $order->userBooking($user->id) ?>

		<table class="table table-striped booking-editor">
			<thead>
				<th width="33%"></th>
				<th width="33%"></th>
				<th width="33%"></th>
			</thead>
			<tbody>
				@foreach($o->products as $product)
					@if($product->variants->isEmpty() == true)

						<tr class="booking-product">
							<td>
								<input type="hidden" name="product-partitioning" value="{{ $product->product->portion_quantity }}" class="skip-on-submit" />
								<input type="hidden" name="product-price" value="{{ $product->product->price + $product->product->transport }}" class="skip-on-submit" />

								<label class="static-label">{{ $product->product->name }}</label>
							</td>

							<td>
								<label class="static-label booking-product-booked">{{ $product->quantity }}</label>
							</td>

							<td>
								<div class="input-group booking-product-quantity">
									<input type="number" step="any" min="0" class="form-control" name="{{ $product->product->id }}" value="{{ $product->delivered }}" />
									<div class="input-group-addon">{{ $product->product->printableMeasure() }}</div>
								</div>
							</td>
						</tr>

					@else

						@foreach($product->variants as $var)
							<tr class="booking-product">
								<td>
									<input type="hidden" name="product-partitioning" value="{{ $product->product->portion_quantity }}" class="skip-on-submit" />
									<input type="hidden" name="product-price" value="{{ $product->product->price + $product->product->transport }}" class="skip-on-submit" />

									<label class="static-label">{{ $product->product->name }}: {{ $var->printableName() }}</label>
								</td>

								<td>
									<label class="static-label booking-product-booked">{{ $var->quantity }}</label>
								</td>

								<td>
									<div class="input-group booking-product-quantity">
										<input type="number" step="any" min="0" class="form-control" name="{{ $var->id }}" value="{{ $var->delivered }}" />
										<div class="input-group-addon">{{ $product->product->printableMeasure() }}</div>
									</div>
								</td>
							</tr>
						@endforeach

					@endif
				@endforeach

				<tr class="hidden booking-product fit-add-product">
					<td>
						<input type="hidden" name="product-partitioning" value="" class="skip-on-submit" />
						<input type="hidden" name="product-price" value="" class="skip-on-submit" />

						<select class="form-control">
							<option value="-1">Seleziona un Prodotto</option>
							@foreach($order->products as $product)
							<option value="{{ $product->id }}">{{ $product->name }}</option>
							@endforeach
						</select>
					</td>

					<td></td>

					<td>
						<div class="input-group booking-product-quantity">
							<input type="number" class="form-control" name="" value="0" />
							<div class="input-group-addon">?</div>
						</div>
					</td>
				</tr>
			</tbody>
			<tfoot>
				<tr>
					<th><button class="btn btn-default add-booking-product">Aggiungi Prodotto</button></th>
					<th></th>
					<th class="text-right">Totale: <span class="booking-total">{{ printablePrice($o->delivered) }}</span> €</th>
				</tr>
			</tfoot>
		</table>
	@endforeach

	<div class="row">
		<div class="col-md-12">
			<div class="btn-group pull-right main-form-buttons" role="group" aria-label="Opzioni">
				<button class="btn btn-default preload-quantities">Carica Quantità Prenotate</button>
				<button type="submit" class="btn btn-success">Salva</button>
			</div>
		</div>
	</div>
</form>
