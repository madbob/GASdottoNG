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
				<th width="35%"></th>
				<th width="25%"></th>
				<th width="15%"></th>
			</thead>
			<tbody>
				@foreach($order->products as $product)
				<tr class="booking-product">
					<td>
						<label class="static-label">{{ $product->name }}</label>
					</td>

					<td>
						@include('booking.quantityselectrow', ['product' => $product, 'populate' => true])
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
				<button type="button" class="btn btn-danger delete-booking">Annulla Prenotazione</button>
				<button type="submit" class="btn btn-success saving-button">Salva</button>
			</div>
		</div>
	</div>
</form>
